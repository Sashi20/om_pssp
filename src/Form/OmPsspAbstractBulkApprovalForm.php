<?php

/**
 * @file
 * Contains \Drupal\om_pssp\Form\OmPsspAbstractBulkApprovalForm.
 */

namespace Drupal\om_pssp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class OmPsspAbstractBulkApprovalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'om_pssp_abstract_bulk_approval_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $options_first = _bulk_list_of_om_pssp_project();
    $selected = !$form_state->getValue(['om_pssp_project']) ? $form_state->getValue([
      'om_pssp_project'
      ]) : key($options_first);
    $form = [];
    $form['om_pssp_project'] = [
      '#type' => 'select',
      '#title' => t('Title of the power system simulation project'),
      '#options' => _bulk_list_of_om_pssp_project(),
      '#default_value' => $selected,
      '#ajax' => [
        'callback' => 'ajax_bulk_om_pssp_abstract_details_callback'
        ],
      '#suffix' => '<div id="ajax_selected_om_pssp"></div><div id="ajax_selected_om_pssp_pdf"></div>',
    ];
    $form['om_pssp_actions'] = [
      '#type' => 'select',
      '#title' => t('Please select action for power system simulation project'),
      '#options' => _bulk_list_om_pssp_actions(),
      '#default_value' => 0,
      '#prefix' => '<div id="ajax_selected_om_pssp_action" style="color:red;">',
      '#suffix' => '</div>',
      '#states' => [
        'invisible' => [
          ':input[name="om_pssp_project"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => t('Please specify the reason for marking resubmit/disapproval'),
      '#prefix' => '<div id= "message_submit">',
      '#states' => [
        'visible' => [
          [
            ':input[name="om_pssp_actions"]' => [
              'value' => 3
              ]
            ],
          'or',
          [':input[name="om_pssp_actions"]' => ['value' => 2]],
        ]
        ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
      '#states' => [
        'invisible' => [
          ':input[name="lab"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $msg = '';
    $root_path = om_pssp_path();
    if ($form_state->get(['clicked_button', '#value']) == 'Submit') {
      if ($form_state->getValue(['om_pssp_project']))
        // om_pssp_abstract_del_lab_pdf($form_state['values']['om_pssp_project']);
 {
        if (\Drupal::currentUser()->hasPermission('om pssp bulk manage abstract')) {
          $query = \Drupal::database()->select('om_pssp_proposal');
          $query->fields('om_pssp_proposal');
          $query->condition('id', $form_state->getValue(['om_pssp_project']));
          $user_query = $query->execute();
          $user_info = $user_query->fetchObject();
          $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($user_info->uid);
          if ($form_state->getValue(['om_pssp_actions']) == 1) {
            // approving entire project //
            $query = \Drupal::database()->select('om_pssp_submitted_abstracts');
            $query->fields('om_pssp_submitted_abstracts');
            $query->condition('proposal_id', $form_state->getValue(['om_pssp_project']));
            $abstracts_q = $query->execute();
            $experiment_list = '';
            while ($abstract_data = $abstracts_q->fetchObject()) {
              \Drupal::database()->query("UPDATE {om_pssp_submitted_abstracts} SET abstract_approval_status = 1, is_submitted = 1, approver_uid = :approver_uid WHERE id = :id", [
                ':approver_uid' => $user->uid,
                ':id' => $abstract_data->id,
              ]);
              \Drupal::database()->query("UPDATE {om_pssp_submitted_abstracts_file} SET file_approval_status = 1, approvar_uid = :approver_uid WHERE submitted_abstract_id = :submitted_abstract_id", [
                ':approver_uid' => $user->uid,
                ':submitted_abstract_id' => $abstract_data->id,
              ]);
            } //$abstract_data = $abstracts_q->fetchObject()
            drupal_goto('powersystems/pssp/manage-proposal/all');
            \Drupal::messenger()->addStatus(t('Approved power system simulation project.'));
            // email 
            // @FIXME
            // // @FIXME
            // // This looks like another module's variable. You'll need to rewrite this call
            // // to ensure that it uses the correct configuration object.
            // $email_subject = t('[!site_name][power system simulation Project] Your uploaded power system simulation project have been approved', array(
            // 						'!site_name' => variable_get('site_name', '')
            // 					));

            // @FIXME
            // // @FIXME
            // // This looks like another module's variable. You'll need to rewrite this call
            // // to ensure that it uses the correct configuration object.
            // $email_body = array(
            // 						0 => t('
            // 
            // Dear !user_name,
            // 
            // Congratulations!
            // Your simulation and abstract for Power Systems Simulation Project at FOSSEE with the following details have been approved.
            // 
            // Full Name: ' . $user_info->name_title . ' ' . $user_info->contributor_name . '
            // Email : ' . $user_data->mail . '
            // University/Institute : ' . $user_info->university . '
            // City : ' . $user_info->city . '
            // 
            // Project Title  : ' . $user_info->project_title . '
            // Description of the simulation: ' . $user_info->description .'
            // 
            // Kindly send us the internship forms as early as possible for processing your honorarium on time. In case you have already sent these forms, please share the the consignment number or tracking id with us.
            // 
            // Note: It will take upto 30 days from the time we receive your forms, to process your honorarium.
            // 
            // 
            // Best Wishes,
            // 
            // !site_name Power Systems Team,
            // FOSSEE,IIT Bombay', array(
            // 							'!site_name' => variable_get('site_name', ''),
            // 							'!user_name' => $user_data->name
            // 						))
            // 					);

            /** sending email when everything done **/
            $email_to = $user_data->mail;
            $from = \Drupal::config('om_pssp.settings')->get('om_pssp_from_email');
            $bcc = \Drupal::config('om_pssp.settings')->get('om_pssp_emails');
            $cc = \Drupal::config('om_pssp.settings')->get('om_pssp_cc_emails');
            $params['standard']['subject'] = $email_subject;
            $params['standard']['body'] = $email_body;
            $params['standard']['headers'] = [
              'From' => $from,
              'MIME-Version' => '1.0',
              'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
              'Content-Transfer-Encoding' => '8Bit',
              'X-Mailer' => 'Drupal',
              'Cc' => $cc,
              'Bcc' => $bcc,
            ];
            if (!drupal_mail('om_pssp', 'standard', $email_to, language_default(), $params, $from, TRUE)) {
              $msg = \Drupal::messenger()->addError('Error sending email message.');
            } //!drupal_mail('om_pssp', 'standard', $email_to, language_default(), $params, $from, TRUE)
          } //$form_state['values']['om_pssp_actions'] == 1
          elseif ($form_state->getValue(['om_pssp_actions']) == 2) {
            if (strlen(trim($form_state->getValue(['message']))) <= 30) {
              $form_state->setErrorByName('message', t(''));
              $msg = \Drupal::messenger()->addError("Please mention the reason for marking resubmit. Minimum 30 character required");
              return $msg;
            }
            //pending review entire project 
            $query = \Drupal::database()->select('om_pssp_submitted_abstracts');
            $query->fields('om_pssp_submitted_abstracts');
            $query->condition('proposal_id', $form_state->getValue(['om_pssp_project']));
            $abstracts_q = $query->execute();
            $experiment_list = '';
            while ($abstract_data = $abstracts_q->fetchObject()) {
              \Drupal::database()->query("UPDATE {om_pssp_submitted_abstracts} SET abstract_approval_status = 0, is_submitted = 0, approver_uid = :approver_uid WHERE id = :id", [
                ':approver_uid' => $user->uid,
                ':id' => $abstract_data->id,
              ]);
              \Drupal::database()->query("UPDATE {om_pssp_proposal} SET is_submitted = 0, approver_uid = :approver_uid WHERE id = :id", [
                ':approver_uid' => $user->uid,
                ':id' => $abstract_data->proposal_id,
              ]);
              \Drupal::database()->query("UPDATE {om_pssp_submitted_abstracts_file} SET file_approval_status = 0, approvar_uid = :approver_uid WHERE submitted_abstract_id = :submitted_abstract_id", [
                ':approver_uid' => $user->uid,
                ':submitted_abstract_id' => $abstract_data->id,
              ]);
            } //$abstract_data = $abstracts_q->fetchObject()
            \Drupal::messenger()->addStatus(t('Resubmit the project files'));
            // email 
            // @FIXME
            // // @FIXME
            // // This looks like another module's variable. You'll need to rewrite this call
            // // to ensure that it uses the correct configuration object.
            // $email_subject = t('[!site_name][power system simulation Project] Your uploaded power system simulation project have been marked as pending', array(
            // 						'!site_name' => variable_get('site_name', '')
            // 					));

            // @FIXME
            // // @FIXME
            // // This looks like another module's variable. You'll need to rewrite this call
            // // to ensure that it uses the correct configuration object.
            // $email_body = array(
            // 						0 => t('
            // 
            // Dear !user_name,
            // 
            // Kindly resubmit the project files for the project: ' . $user_info->project_title . '.
            // Description of the simulation: ' . $user_info->description . '
            // 
            // Reason: ' . $form_state['values']['message'] . '
            // 
            // Looking forward for the re-submission from you with the above suggested changes.
            // 
            // Best Wishes,
            // 
            // !site_name Power Systems Team,
            // FOSSEE,IIT Bombay', array(
            // 							'!site_name' => variable_get('site_name', ''),
            // 							'!user_name' => $user_data->name
            // 						))
            // 					);

            /** sending email when everything done **/
            $email_to = $user_data->mail;
            $from = \Drupal::config('om_pssp.settings')->get('om_pssp_from_email');
            $bcc = \Drupal::config('om_pssp.settings')->get('om_pssp_emails');
            $cc = \Drupal::config('om_pssp.settings')->get('om_pssp_cc_emails');
            $params['standard']['subject'] = $email_subject;
            $params['standard']['body'] = $email_body;
            $params['standard']['headers'] = [
              'From' => $from,
              'MIME-Version' => '1.0',
              'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
              'Content-Transfer-Encoding' => '8Bit',
              'X-Mailer' => 'Drupal',
              'Cc' => $cc,
              'Bcc' => $bcc,
            ];
            if (!drupal_mail('om_pssp', 'standard', $email_to, language_default(), $params, $from, TRUE)) {
              \Drupal::messenger()->addError('Error sending email message.');
            } //!drupal_mail('om_pssp', 'standard', $email_to, language_default(), $params, $from, TRUE)
          } //$form_state['values']['om_pssp_actions'] == 2
          elseif ($form_state->getValue(['om_pssp_actions']) == 3) //disapprove and delete entire power system simulation project
 {
            if (strlen(trim($form_state->getValue(['message']))) <= 30) {
              $form_state->setErrorByName('message', t(''));
              $msg = \Drupal::messenger()->addError("Please mention the reason for disapproval. Minimum 30 character required");
              return $msg;
            } //strlen(trim($form_state['values']['message'])) <= 30
            if (!\Drupal::currentUser()->hasPermission('om pssp bulk delete code')) {
              $msg = \Drupal::messenger()->addError(t('You do not have permission to Bulk Dis-Approved and Deleted Entire Lab.'));
              return $msg;
            } //!user_access('om_pssp bulk delete code')
            if (om_pssp_abstract_delete_project($form_state->getValue(['om_pssp_project']))) //////
 {
              \Drupal::messenger()->addStatus(t('Dis-Approved and Deleted Entire power system simulation project.'));
              // @FIXME
              // // @FIXME
              // // This looks like another module's variable. You'll need to rewrite this call
              // // to ensure that it uses the correct configuration object.
              // $email_subject = t('[!site_name][power system simulation Project] Your uploaded power system simulation project have been marked as dis-approved', array(
              // 						'!site_name' => variable_get('site_name', '')
              // 					));

              // @FIXME
              // // @FIXME
              // // This looks like another module's variable. You'll need to rewrite this call
              // // to ensure that it uses the correct configuration object.
              // $email_body = array(
              // 						0 => t('
              // Dear !user_name,
              // 
              // We regret to inform you that your simulation and abstract for Power Systems Simulation Project at FOSSEE with the following details have been disapproved:
              // 
              // Full Name: ' . $user_info->name_title . ' ' . $user_info->contributor_name . '
              // Email : ' . $user_data->mail . '
              // University/Institute : ' . $user_info->university . '
              // City : ' . $user_info->city . '
              // 
              // Project Title  : ' . $user_info->project_title . '
              // Description of the simulation: ' . $user_info->description .'
              // 
              // Reason for dis-approval: ' . $form_state['values']['message'] . '
              // 
              // Kindly note that the incorrect files will be deleted from all our databases.
              // 
              // Thank you for participating in the Power Systems Simulation Project. You are welcome to submit a new proposal.
              // 
              // Best Wishes,
              // 
              // !site_name Power Systems Team,
              // FOSSEE,IIT Bombay', array(
              // 						'!site_name' => variable_get('site_name', ''),
              // 						'!user_name' => $user_data->name
              // 											))
              // 					);

              $email_to = $user_data->mail;
              $from = \Drupal::config('om_pssp.settings')->get('om_pssp_from_email');
              $bcc = \Drupal::config('om_pssp.settings')->get('om_pssp_emails');
              $cc = \Drupal::config('om_pssp.settings')->get('om_pssp_cc_emails');
              $params['standard']['subject'] = $email_subject;
              $params['standard']['body'] = $email_body;
              $params['standard']['headers'] = [
                'From' => $from,
                'MIME-Version' => '1.0',
                'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
                'Content-Transfer-Encoding' => '8Bit',
                'X-Mailer' => 'Drupal',
                'Cc' => $cc,
                'Bcc' => $bcc,
              ];
              if (!drupal_mail('om_pssp', 'standard', $email_to, language_default(), $params, $from, TRUE)) {
                \Drupal::messenger()->addError('Error sending email message.');
              }
            } //om_pssp_abstract_delete_project($form_state['values']['om_pssp_project'])
            else {
              \Drupal::messenger()->addError(t('Error Dis-Approving and Deleting Entire power system simulation project.'));
            }
            // email 

          } //$form_state['values']['om_pssp_actions'] == 3
        }
      } //user_access('om_pssp project bulk manage code')
      return $msg;
    } //$form_state['clicked_button']['#value'] == 'Submit'
  }

}
?>
