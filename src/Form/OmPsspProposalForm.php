<?php

/**
 * @file
 * Contains \Drupal\om_pssp\Form\OmPsspProposalForm.
 */

namespace Drupal\om_pssp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class OmPsspProposalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'om_pssp_proposal_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state, $no_js_use = NULL) {
    $user = \Drupal::currentUser();
    /************************ start approve book details ************************/
    if ($user->uid == 0) {
      $msg = \Drupal::messenger()->addError(t('It is mandatory to ' . \Drupal\Core\Link::fromTextAndUrl('login', \Drupal\Core\Url::fromRoute('user.page')) . ' on this website to access the power systems simulation proposal form. If you are new user please create a new account first.'));
      //drupal_goto('powersystems/pssp');
      drupal_goto('user');
      return $msg;
    } //$user->uid == 0
    $query = \Drupal::database()->select('om_pssp_proposal');
    $query->fields('om_pssp_proposal');
    $query->condition('uid', $user->uid);
    $query->orderBy('id', 'DESC');
    $query->range(0, 1);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();
    if ($proposal_data) {
      if ($proposal_data->approval_status == 0 || $proposal_data->approval_status == 1) {
        \Drupal::messenger()->addStatus(t('We have already received your proposal.'));
        drupal_goto('powersystems');
        return;
      } //$proposal_data->approval_status == 0 || $proposal_data->approval_status == 1
    } //$proposal_data
    $imp = t('<span style="color: red;">*This is a mandatory field</span>');
    $form['#attributes'] = ['enctype' => "multipart/form-data"];
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
    ];
    $form['contributor_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name of the contributor'),
      '#size' => 350,
      '#attributes' => [
        'placeholder' => t('Enter your full name.....')
        ],
      '#maxlength' => 350,
      '#required' => TRUE,
      '#description' => t('<span  style="color:red;">Kindly enter the names (maximum four) in the following format: Name1, Name2, Name3 and Name4</span>'),
    ];
    $form['contributor_contact_no'] = [
      '#type' => 'textfield',
      '#title' => t('Contact No.'),
      '#size' => 10,
      '#attributes' => [
        'placeholder' => t('Enter your contact number')
        ],
      '#maxlength' => 250,
    ];
    $form['gender'] = [
      '#type' => 'select',
      '#title' => t('Gender'),
      '#options' => [
        'Male' => 'Male',
        'Female' => 'Female',
        'Other' => 'Other',
      ],
      '#required' => TRUE,
    ];
    $form['month_year_of_degree'] = [
      '#type' => 'date_popup',
      '#title' => t('Month and year of award of degree'),
      '#date_label_position' => '',
      '#description' => '',
      '#default_value' => '',
      '#date_format' => 'M-Y',
      '#date_increment' => 0,
      '#date_year_range' => '1960: +22',
      '#required' => FALSE,
    ];
    $form['contributor_email_id'] = [
      '#type' => 'textfield',
      '#title' => t('Email'),
      '#size' => 30,
      '#value' => $user->mail,
      '#disabled' => TRUE,
    ];
    $form['university'] = [
      '#type' => 'textfield',
      '#title' => t('University/ Institute'),
      '#size' => 80,
      '#maxlength' => 200,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Insert full name of your institute/ university.... '
        ],
    ];

    $form['country'] = [
      '#type' => 'select',
      '#title' => t('Country'),
      '#options' => [
        'India' => 'India',
        'Others' => 'Others',
      ],
      '#required' => TRUE,
      '#tree' => TRUE,
      '#validated' => TRUE,
    ];
    $form['other_country'] = [
      '#type' => 'textfield',
      '#title' => t('Other than India'),
      '#size' => 100,
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
      '#description' => $imp,
    ];
    $form['other_state'] = [
      '#type' => 'textfield',
      '#title' => t('State other than India'),
      '#size' => 100,
      '#attributes' => [
        'placeholder' => t('Enter your state/region name')
        ],
      '#states' => [
        'visible' => [
          ':input[name="country"]' => [
            'value' => 'Others'
            ]
          ]
        ],
      '#description' => $imp,
    ];
    $form['other_city'] = [
      '#type' => 'textfield',
      '#title' => t('City other than India'),
      '#size' => 100,
      '#attributes' => [
        'placeholder' => t('Enter your city name')
        ],
      '#states' => [
        'visible' => [
          ':input[name="country"]' => [
            'value' => 'Others'
            ]
          ]
        ],
      '#description' => $imp,
    ];
    $form['all_state'] = [
      '#type' => 'select',
      '#title' => t('State'),
      '#options' => _pssp_list_of_states(),
      '#validated' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="country"]' => [
            'value' => 'India'
            ]
          ]
        ],
      '#description' => $imp,
    ];
    $form['city'] = [
      '#type' => 'select',
      '#title' => t('City'),
      '#options' => _pssp_list_of_cities(),
      '#validated' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="country"]' => [
            'value' => 'India'
            ]
          ]
        ],
      '#description' => $imp,
    ];
    $form['pincode'] = [
      '#type' => 'textfield',
      '#title' => t('Pincode'),
      '#size' => 6,
    ];
    $form['project_guide_name'] = [
      '#type' => 'textfield',
      '#title' => t('Project guide'),
      '#size' => 250,
      '#attributes' => [
        'placeholder' => t('Enter full name of your project guide')
        ],
      '#maxlength' => 250,
    ];
    $form['project_guide_email_id'] = [
      '#type' => 'textfield',
      '#title' => t('Project guide email'),
      '#size' => 30,
      '#attributes' => [
        'placeholder' => 'Enter the email id of your project guide.... '
        ],
    ];
    $form['project_guide_university'] = [
      '#type' => 'textfield',
      '#title' => t('Project Guide University/ Institute'),
      '#size' => 80,
      '#maxlength' => 200,
      '#attributes' => [
        'placeholder' => 'Enter full name of the institute/ university of your project guide.... '
        ],
    ];

    /***************************************************************************/
    $form['hr'] = [
      '#type' => 'item',
      '#markup' => '<hr>',
    ];
    $form['project_title'] = [
      '#type' => 'textarea',
      '#title' => t('Simulation Title'),
      '#size' => 80,
      '#description' => t('Maximum character limit is 80, minimum character 10'),
      '#required' => TRUE,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => t('Description of Proposed Simulation'),
      '#size' => 250,
      '#description' => t('Maximum character limit is 250, minimum 60'),
      '#required' => TRUE,
    ];
    $form['reference'] = [
      '#type' => 'textfield',
      '#title' => t('Reference'),
      '#size' => 250,
      '#required' => TRUE,
      '#description' => t('Example: Plain Text, DOI, IEEE format etc.'),
      '#attributes' => [
        'placeholder' => 'The links to the documents or websites which are referenced while proposing this project.'
        ],
    ];
    $form['version'] = [
      '#type' => 'select',
      '#title' => t('OM Version'),
      '#options' => _pssp_list_of_software_version(),
      '#required' => TRUE,
    ];
    /*$form['operating_system'] = array(
		'#type' => 'select',
		'#title' => t('Operating System'),
		'#options' => array(
			'Ubuntu' => 'Ubuntu',
			'Windows' => 'Windows'
		),
		'#required' => TRUE,
		'#tree' => TRUE,
		//'#validated' => TRUE
	);*/
    $form['samplefile'] = [
      '#type' => 'fieldset',
      '#title' => t('Upload Abstract<span style="color:red;">&nbsp;*</span>'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    // @FIXME
    // // @FIXME
    // // This looks like another module's variable. You'll need to rewrite this call
    // // to ensure that it uses the correct configuration object.
    // $form['samplefile']['samplefilepath'] = array(
    // 		'#type' => 'file',
    // 		'#size' => 48,
    // 		'#description' => t('<span style="font-size:16px;">For a sample of the abstract <span style="font-size:20px;">&rarr;</span>
    // 			<a href="https://static.fossee.in/om/internship-forms/05_PSS_Abstract%20Submission%20Form.pdf">
    // 			Click here</a></span>' . '<br />' . 'Upload filenames with allowed extensions only. No spaces or any special characters allowed in filename.') . '<br />' . t('<span style="color:red;">Allowed file extensions : ') . variable_get('resource_upload_extensions', '') . '</span>'
    // 	);

    $form['term_condition'] = [
      '#type' => 'checkboxes',
      '#title' => t('Terms And Conditions'),
      '#options' => [
        'status' => t('<a href="/powersystems/pssp/terms-and-conditions" target="_blank">I agree to the Terms and Conditions</a>')
        ],
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    return $form;
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $project_title = $form_state->getValue(['project_title']);
    /*$proposar_name = $form_state['values']['name_title'] . ' ' . $form_state['values']['contributor_name'];
	$directory_name = _pssp_dir_name($project_title, $proposar_name);*/
    //var_dump($directory_name);die;
    $query = \Drupal::database()->select('om_pssp_proposal');
    $query->fields('om_pssp_proposal');
    $query->condition('project_title', $project_title);
    $query->condition(db_or()->condition('approval_status', 1)->condition('approval_status', 3));
    $result = $query->execute()->rowCount();
    //var_dump($result);die;
    if ($result >= 1) {
      $form_state->setErrorByName('project_title', t('Project title name already exists'));
      return;
    }
    if ($form_state->getValue(['term_condition']) == '1') {
      $form_state->setErrorByName('term_condition', t('Please check the terms and conditions'));
      // $form_state['values']['country'] = $form_state['values']['other_country'];
    } //$form_state['values']['term_condition'] == '1'
    if ($form_state->getValue([
      'country'
      ]) == 'Others') {
      if ($form_state->getValue(['other_country']) == '') {
        $form_state->setErrorByName('other_country', t('Enter country name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['other_country'] == ''
      else {
        $form_state->setValue(['country'], $form_state->getValue([
          'other_country'
          ]));
      }
      if ($form_state->getValue(['other_state']) == '') {
        $form_state->setErrorByName('other_state', t('Enter state name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['other_state'] == ''
      else {
        $form_state->setValue(['all_state'], $form_state->getValue([
          'other_state'
          ]));
      }
      if ($form_state->getValue(['other_city']) == '') {
        $form_state->setErrorByName('other_city', t('Enter city name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['other_city'] == ''
      else {
        $form_state->setValue(['city'], $form_state->getValue(['other_city']));
      }
    } //$form_state['values']['country'] == 'Others'
    else {
      if ($form_state->getValue(['country']) == '0') {
        $form_state->setErrorByName('country', t('Select country name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['country'] == ''
      if ($form_state->getValue([
        'all_state'
        ]) == '0') {
        $form_state->setErrorByName('all_state', t('Select state name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['all_state'] == ''
      if ($form_state->getValue([
        'city'
        ]) == '0') {
        $form_state->setErrorByName('city', t('Select city name'));
        // $form_state['values']['country'] = $form_state['values']['other_country'];
      } //$form_state['values']['city'] == ''
    }
    //Validation for project title
    $form_state->setValue(['project_title'], trim($form_state->getValue([
      'project_title'
      ])));
    if ($form_state->getValue(['project_title']) != '') {
      if (strlen($form_state->getValue(['project_title'])) > 80) {
        $form_state->setErrorByName('project_title', t('Maximum charater limit is 80 charaters only, please check the length of the project title'));
      } //strlen($form_state['values']['project_title']) > 250
      else {
        if (strlen($form_state->getValue(['project_title'])) < 10) {
          $form_state->setErrorByName('project_title', t('Minimum charater limit is 10 charaters, please check the length of the project title'));
        }
      } //strlen($form_state['values']['project_title']) < 10
    } //$form_state['values']['project_title'] != ''
    else {
      $form_state->setErrorByName('project_title', t('Project title shoud not be empty'));
    }
    $form_state->setValue(['description'], trim($form_state->getValue([
      'description'
      ])));
    if ($form_state->getValue(['description']) != '') {
      if (strlen($form_state->getValue(['description'])) > 250) {
        $form_state->setErrorByName('description', t('Maximum charater limit is 250 charaters only, please check the length of the description'));
      } //strlen($form_state['values']['project_title']) > 250
      else {
        if (strlen($form_state->getValue(['description'])) < 60) {
          $form_state->setErrorByName('description', t('Minimum charater limit is 60 charaters, please check the length of the description'));
        }
      } //strlen($form_state['values']['project_title']) < 10
    } //$form_state['values']['project_title'] != ''
    else {
      $form_state->setErrorByName('description', t('Description shoud not be empty'));
    }
    if (isset($_FILES['files'])) {
      /* check if atleast one source or result file is uploaded */
      if (!($_FILES['files']['name']['samplefilepath'])) {
        $form_state->setErrorByName('samplefilepath', t('Please upload the abstract file.'));
      }
      /* check for valid filename extensions */
      foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
        if ($file_name) {
          /* checking file type */
          // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// $allowed_extensions_str = variable_get('resource_upload_extensions', '');

          $allowed_extensions = explode(',', $allowed_extensions_str);
          $fnames = explode('.', strtolower($_FILES['files']['name'][$file_form_name]));
          $temp_extension = end($fnames);
          if (!in_array($temp_extension, $allowed_extensions)) {
            $form_state->setErrorByName($file_form_name, t('Only file with ' . $allowed_extensions_str . ' extensions can be uploaded.'));
          }
          if ($_FILES['files']['size'][$file_form_name] <= 0) {
            $form_state->setErrorByName($file_form_name, t('File size cannot be zero.'));
          }
          /* check if valid file name */
          if (!textbook_companion_check_valid_filename($_FILES['files']['name'][$file_form_name])) {
            $form_state->setErrorByName($file_form_name, t('Invalid file name specified. Only alphabets and numbers are allowed as a valid filename.'));
          }
        } //$file_name
      } //$_FILES['files']['name'] as $file_form_name => $file_name
    }
    return $form_state;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $root_path = om_pssp_path();
    if (!$user->uid) {
      \Drupal::messenger()->addError('It is mandatory to login on this website to access the proposal form');
      return;
    } //!$user->uid
	/*if ($form_state['values']['version'] == 'Old version')
	{
		$form_state['values']['version'] = trim($form_state['values']['older']);
	} *///$form_state['values']['version'] == 'Old version'
	/* inserting the user proposal */
    $v = $form_state->getValues();
    $project_title = trim($v['project_title']);
    $proposar_name = $v['name_title'] . ' ' . $v['contributor_name'];
    $university = $v['university'];
    $month_year_of_degree = $v['month_year_of_degree'];
    $directory_name = _pssp_dir_name($project_title, $proposar_name);
    $result = "INSERT INTO {om_pssp_proposal} 
    (
    uid, 
    approver_uid,
    name_title, 
    contributor_name,
    contact_no,
    gender,
    month_year_of_degree,
    university,
    city, 
    pincode, 
    state, 
    country,
    version,
    project_guide_name,
    project_guide_email_id,
    project_guide_university,
    project_title, 
    description,
    directory_name,
    approval_status,
    is_completed, 
    dissapproval_reason,
    creation_date, 
    approval_date,
    samplefilepath,
    reference
    ) VALUES
    (
    :uid, 
    :approver_uid, 
    :name_title, 
    :contributor_name, 
    :contact_no,
    :gender,
    :month_year_of_degree,
    :university, 
    :city, 
    :pincode, 
    :state,  
    :country,
    :version,
    :project_guide_name,
    :project_guide_email_id,
    :project_guide_university,
    :project_title, 
    :description,
    :directory_name,
    :approval_status,
    :is_completed, 
    :dissapproval_reason,
    :creation_date, 
    :approval_date,
    :samplefilepath,
    :reference
    )";
    $args = [
      ":uid" => $user->uid,
      ":approver_uid" => 0,
      ":name_title" => $v['name_title'],
      ":contributor_name" => _pssp_sentence_case(trim($v['contributor_name'])),
      ":contact_no" => $v['contributor_contact_no'],
      ":gender" => $v['gender'],
      ":month_year_of_degree" => $month_year_of_degree,
      ":university" => _pssp_sentence_case($v['university']),
      ":city" => $v['city'],
      ":pincode" => $v['pincode'],
      ":state" => $v['all_state'],
      ":country" => $v['country'],
      ":version" => $v['version'],
      ":project_guide_name" => _pssp_sentence_case($v['project_guide_name']),
      ":project_guide_email_id" => trim($v['project_guide_email_id']),
      ":project_guide_university" => trim($v['project_guide_university']),
      ":project_title" => $v['project_title'],
      ":description" => _pssp_sentence_case($v['description']),
      ":directory_name" => $directory_name,
      ":approval_status" => 0,
      ":is_completed" => 0,
      ":dissapproval_reason" => "NULL",
      ":creation_date" => time(),
      ":approval_date" => 0,
      ":samplefilepath" => "",
      ":reference" => $v['reference'],
    ];
    //	var_dump($args);die;
    //var_dump($result);die;
    $proposal_id = \Drupal::database()->query($result, $args, $result);
    //var_dump($args);die;

    $dest_path = $directory_name . '/';
    $dest_path1 = $root_path . $dest_path;
    //var_dump($dest_path1);die;	
    if (!is_dir($root_path . $dest_path)) {
      mkdir($root_path . $dest_path);
    }
    /* uploading files */
    foreach ($_FILES['files']['name'] as $file_form_name => $file_name) {
      if ($file_name) {
        /* checking file type */
        //$file_type = 'S';
        if (file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])) {
          \Drupal::messenger()->addError(t("Error uploading file. File !filename already exists.", [
            '!filename' => $_FILES['files']['name'][$file_form_name]
            ]));
          //unlink($root_path . $dest_path . $_FILES['files']['name'][$file_form_name]);
        } //file_exists($root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
			/* uploading file */
        if (move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])) {
          $query = "UPDATE {om_pssp_proposal} SET samplefilepath = :samplefilepath WHERE id = :id";
          $args = [
            ":samplefilepath" => $dest_path . $_FILES['files']['name'][$file_form_name],
            ":id" => $proposal_id,
          ];

          $updateresult = \Drupal::database()->query($query, $args);
          //var_dump($args);die;

          \Drupal::messenger()->addStatus($file_name . ' uploaded successfully.');
        } //move_uploaded_file($_FILES['files']['tmp_name'][$file_form_name], $root_path . $dest_path . $_FILES['files']['name'][$file_form_name])
        else {
          \Drupal::messenger()->addError('Error uploading file : ' . $dest_path . '/' . $file_name);
        }
      } //$file_name
    } //$_FILES['files']['name'] as $file_form_name => $file_name
    if (!$proposal_id) {
      \Drupal::messenger()->addError(t('Error receiving your proposal. Please try again.'));
      return;
    } //!$proposal_id
	/* sending email */
    $email_to = $user->mail;
    $form = \Drupal::config('om_pssp.settings')->get('om_pssp_from_email');
    $bcc = \Drupal::config('om_pssp.settings')->get('om_pssp_emails');
    $cc = \Drupal::config('om_pssp.settings')->get('om_pssp_cc_emails');
    $params['om_pssp_proposal_received']['proposal_id'] = $proposal_id;
    $params['om_pssp_proposal_received']['user_id'] = $user->uid;
    $params['om_pssp_proposal_received']['headers'] = [
      'From' => $form,
      'MIME-Version' => '1.0',
      'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
      'Content-Transfer-Encoding' => '8Bit',
      'X-Mailer' => 'Drupal',
      'Cc' => $cc,
      'Bcc' => $bcc,
    ];
    if (!drupal_mail('om_pssp', 'om_pssp_proposal_received', $email_to, user_preferred_language($user), $params, $form, TRUE)) {
      \Drupal::messenger()->addError('Error sending email message.');
    }
    \Drupal::messenger()->addStatus(t('We have received your  power systems simulation proposal. We will get back to you soon.'));
    drupal_goto('powersystems');
  }

}
?>
