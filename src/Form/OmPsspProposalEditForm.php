<?php

/**
 * @file
 * Contains \Drupal\om_pssp\Form\OmPsspProposalEditForm.
 */

namespace Drupal\om_pssp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class OmPsspProposalEditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'om_pssp_proposal_edit_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    /* get current proposal */
    $proposal_id = (int) arg(4);
    //$proposal_q = db_query("SELECT * FROM {om_pssp_proposal} WHERE id = %d", $proposal_id);
    $query = \Drupal::database()->select('om_pssp_proposal');
    $query->fields('om_pssp_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
      if ($proposal_data = $proposal_q->fetchObject()) {
        /* everything ok */
      } //$proposal_data = $proposal_q->fetchObject()
      else {
        \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
        drupal_goto('powersystems/pssp/manage-proposal');
        return;
      }
    } //$proposal_q
    else {
      \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
      drupal_goto('powersystems/pssp/manage-proposal');
      return;
    }
    $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid);
    $form['name_title'] = [
      '#type' => 'select',
      '#title' => t('Title'),
      '#options' => [
        'Dr' => 'Dr',
        'Prof' => 'Prof',
        'Mr' => 'Mr',
        'Mrs' => 'Mrs',
        'Ms' => 'Ms',
      ],
      '#required' => TRUE,
      '#default_value' => $proposal_data->name_title,
    ];
    $form['contributor_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name of the Proposer'),
      '#size' => 350,
      '#maxlength' => 350,
      '#required' => TRUE,
      '#default_value' => $proposal_data->contributor_name,
    ];
    $form['student_email_id'] = [
      '#type' => 'item',
      '#title' => t('Email'),
      '#markup' => $user_data->mail,
    ];
    $form['month_year_of_degree'] = [
      '#type' => 'date_popup',
      '#title' => t('Month and year of award of degree'),
      '#date_label_position' => '',
      '#description' => '',
      '#default_value' => $proposal_data->month_year_of_degree,
      '#date_format' => 'M-Y',
      '#date_increment' => 0,
      '#date_year_range' => '1960:+0',
      '#datepicker_options' => [
        'maxDate' => 0
        ],
      '#required' => FALSE,
    ];
    $form['university'] = [
      '#type' => 'textfield',
      '#title' => t('University/Institute'),
      '#size' => 200,
      '#maxlength' => 200,
      '#required' => TRUE,
      '#default_value' => $proposal_data->university,
    ];
    if ($proposal_data->country == 'India') {
      $form['country'] = [
        '#type' => 'select',
        '#title' => t('Country'),
        '#options' => [
          'India' => 'India',
          'Others' => 'Others',
        ],
        '#default_value' => $proposal_data->country,
        '#required' => TRUE,
        '#tree' => TRUE,
        '#validated' => TRUE,
      ];
      $form['all_state'] = [
        '#type' => 'select',
        '#title' => t('State'),
        '#options' => _pssp_list_of_states(),
        '#default_value' => $proposal_data->state,
        '#validated' => TRUE,
        '#states' => [
          'visible' => [
            ':input[name="country"]' => [
              'value' => 'India'
              ]
            ]
          ],
      ];
      $form['city'] = [
        '#type' => 'select',
        '#title' => t('City'),
        '#options' => _pssp_list_of_cities(),
        '#default_value' => $proposal_data->city,
        '#states' => [
          'visible' => [
            ':input[name="country"]' => [
              'value' => 'India'
              ]
            ]
          ],
      ];
    }
    else {
      $form['other_country'] = [
        '#type' => 'textfield',
        '#title' => t('Country(Other than India)'),
        '#size' => 100,
        '#default_value' => $proposal_data->country,
        '#attributes' => [
          'placeholder' => t('Enter your country name')
          ],
        '#states' => [
          'visible' => [
            ':input[name="country"]' => [
              'value' => 'Others'
              ]
            ]
          ],
      ];
      $form['other_state'] = [
        '#type' => 'textfield',
        '#title' => t('State(Other than India)'),
        '#size' => 100,
        '#attributes' => [
          'placeholder' => t('Enter your state/region name')
          ],
        '#default_value' => $proposal_data->state,
        '#states' => [
          'visible' => [
            ':input[name="country"]' => [
              'value' => 'Others'
              ]
            ]
          ],
      ];
      $form['other_city'] = [
        '#type' => 'textfield',
        '#title' => t('City(Other than India)'),
        '#size' => 100,
        '#attributes' => [
          'placeholder' => t('Enter your city name')
          ],
        '#default_value' => $proposal_data->city,
        '#states' => [
          'visible' => [
            ':input[name="country"]' => [
              'value' => 'Others'
              ]
            ]
          ],
      ];
    }

    $form['pincode'] = [
      '#type' => 'textfield',
      '#title' => t('Pincode'),
      '#size' => 30,
      '#maxlength' => 6,
      '#default_value' => $proposal_data->pincode,
      '#attributes' => [
        'placeholder' => 'Insert pincode of your city/ village....'
        ],
    ];
    $form['project_title'] = [
      '#type' => 'textarea',
      '#title' => t('Title of the Power systems simulation Project'),
      '#size' => 300,
      '#maxlength' => 350,
      '#required' => TRUE,
      '#default_value' => $proposal_data->project_title,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => t('Description of the Project'),
      '#size' => 250,
      '#maxlength' => 250,
      '#required' => TRUE,
      '#default_value' => $proposal_data->description,
    ];
    $form['reference'] = [
      '#type' => 'textarea',
      '#title' => t('Reference'),
      '#size' => 10000,
      '#attributes' => [
        'placeholder' => 'Links of must be provided....'
        ],
      '#default_value' => $proposal_data->reference,
    ];
    $form['delete_proposal'] = [
      '#type' => 'checkbox',
      '#title' => t('Delete Proposal'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    // @FIXME
    // l() expects a Url object, created from a route name or external URI.
    // $form['cancel'] = array(
    // 		'#type' => 'item',
    // 		'#markup' => l(t('Cancel'), 'powersystems/pssp/manage-proposal')
    // 	);

    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    /* get current proposal */
    $proposal_id = (int) arg(4);
    $query = \Drupal::database()->select('om_pssp_proposal');
    $query->fields('om_pssp_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
      if ($proposal_data = $proposal_q->fetchObject()) {
        /* everything ok */
      } //$proposal_data = $proposal_q->fetchObject()
      else {
        \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
        drupal_goto('powersystems/pssp/manage-proposal');
        return;
      }
    } //$proposal_q
    else {
      \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
      drupal_goto('powersystems/pssp/manage-proposal');
      return;
    }
    /* delete proposal */
    if ($form_state->getValue(['delete_proposal']) == 1) {
      /* sending email */
      $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid);
      $email_to = $user_data->mail;
      $from = \Drupal::config('om_pssp.settings')->get('om_pssp_from_email');
      $bcc = \Drupal::config('om_pssp.settings')->get('om_pssp_emails');
      $cc = \Drupal::config('om_pssp.settings')->get('om_pssp_cc_emails');
      $params['om_pssp_proposal_deleted']['proposal_id'] = $proposal_id;
      $params['om_pssp_proposal_deleted']['user_id'] = $proposal_data->uid;
      $params['om_pssp_proposal_deleted']['headers'] = [
        'From' => $from,
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
        'Content-Transfer-Encoding' => '8Bit',
        'X-Mailer' => 'Drupal',
        'Cc' => $cc,
        'Bcc' => $bcc,
      ];
      if (!drupal_mail('om_pssp', 'om_pssp_proposal_deleted', $email_to, user_preferred_language($user), $params, $from, TRUE)) {
        \Drupal::messenger()->addError('Error sending email message.');
      }
      \Drupal::messenger()->addStatus(t('Power system simulation proposal has been deleted.'));
      if (rrmdir_project($proposal_id) == TRUE) {
        $query = \Drupal::database()->delete('om_pssp_proposal');
        $query->condition('id', $proposal_id);
        $num_deleted = $query->execute();
        \Drupal::messenger()->addStatus(t('Proposal Deleted'));
        drupal_goto('powersystems/pssp/manage-proposal');
        return;
      } //rrmdir_project($proposal_id) == TRUE
    } //$form_state['values']['delete_proposal'] == 1
	/* update proposal */
    $v = $form_state->getValues();
    $str = substr($proposal_data->samplefilepath, strrpos($proposal_data->samplefilepath, '/'));
    $resource_file = ltrim($str, '/');
    $project_title = $v['project_title'];
    $proposar_name = $v['name_title'] . ' ' . $v['contributor_name'];
    $university = $v['university'];
    $directory_names = _pssp_dir_name($project_title, $proposar_name);
    if (PSSP_RenameDir($proposal_id, $directory_names)) {
      $directory_name = $directory_names;
    } //LM_RenameDir($proposal_id, $directory_names)
    else {
      return;
    }
    $samplefilepath = $directory_name . '/' . $resource_file;
    $query = "UPDATE om_pssp_proposal SET 
				name_title=:name_title,
				contributor_name=:contributor_name,
				university=:university,
				city=:city,
				pincode=:pincode,
				state=:state,
				project_title=:project_title,
				description=:description,
				reference=:reference,
				directory_name=:directory_name,
				samplefilepath=:samplefilepath
				WHERE id=:proposal_id";
    $args = [
      ':name_title' => $v['name_title'],
      ':contributor_name' => $v['contributor_name'],
      ':university' => $v['university'],
      ':city' => $v['city'],
      ':pincode' => $v['pincode'],
      ':state' => $v['all_state'],
      ':project_title' => $project_title,
      ':description' => $v['description'],
      ':reference' => $v['reference'],
      ':directory_name' => $directory_name,
      ':samplefilepath' => $samplefilepath,
      ':proposal_id' => $proposal_id,
    ];
    $result = \Drupal::database()->query($query, $args);
    \Drupal::messenger()->addStatus(t('Proposal Updated'));
  }

}
?>
