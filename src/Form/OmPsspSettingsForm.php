<?php

/**
 * @file
 * Contains \Drupal\om_pssp\Form\OmPsspSettingsForm.
 */

namespace Drupal\om_pssp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class OmPsspSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'om_pssp_settings_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form['emails'] = [
      '#type' => 'textfield',
      '#title' => t('(Bcc) Notification emails'),
      '#description' => t('Specify emails id for Bcc option of mail system with comma separated'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => \Drupal::config('om_pssp.settings')->get('om_pssp_emails'),
    ];
    $form['cc_emails'] = [
      '#type' => 'textfield',
      '#title' => t('(Cc) Notification emails'),
      '#description' => t('Specify emails id for Cc option of mail system with comma separated'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => \Drupal::config('om_pssp.settings')->get('om_pssp_cc_emails'),
    ];
    $form['from_email'] = [
      '#type' => 'textfield',
      '#title' => t('Outgoing from email address'),
      '#description' => t('Email address to be display in the from field of all outgoing messages'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => \Drupal::config('om_pssp.settings')->get('om_pssp_from_email'),
    ];
    // @FIXME
    // // @FIXME
    // // This looks like another module's variable. You'll need to rewrite this call
    // // to ensure that it uses the correct configuration object.
    // $form['extensions']['resource_upload'] = array(
    // 		'#type' => 'textfield',
    // 		'#title' => t('Allowed file extensions for uploading resource files'),
    // 		'#description' => t('A comma separated list WITHOUT SPACE of source file extensions that are permitted to be uploaded on the server'),
    // 		'#size' => 50,
    // 		'#maxlength' => 255,
    // 		'#required' => TRUE,
    // 		'#default_value' => variable_get('resource_upload_extensions', '')
    // 	);

    $form['extensions']['abstract_upload'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed file extensions for abstract'),
      '#description' => t('A comma separated list WITHOUT SPACE of pdf file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => \Drupal::config('om_pssp.settings')->get('om_pssp_abstract_upload_extensions'),
    ];
    $form['extensions']['om_pssp_upload'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed extensions for project files'),
      '#description' => t('A comma separated list WITHOUT SPACE of pdf file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => \Drupal::config('om_pssp.settings')->get('om_pssp_project_files_extensions'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    return $form;
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    return;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    \Drupal::configFactory()->getEditable('om_pssp.settings')->set('om_pssp_emails', $form_state->getValue(['emails']))->save();
    \Drupal::configFactory()->getEditable('om_pssp.settings')->set('om_pssp_cc_emails', $form_state->getValue(['cc_emails']))->save();
    \Drupal::configFactory()->getEditable('om_pssp.settings')->set('om_pssp_from_email', $form_state->getValue(['from_email']))->save();
    // @FIXME
    // // @FIXME
    // // This looks like another module's variable. You'll need to rewrite this call
    // // to ensure that it uses the correct configuration object.
    // variable_set('resource_upload_extensions', $form_state['values']['resource_upload']);

    \Drupal::configFactory()->getEditable('om_pssp.settings')->set('om_pssp_abstract_upload_extensions', $form_state->getValue(['abstract_upload']))->save();
    \Drupal::configFactory()->getEditable('om_pssp.settings')->set('om_pssp_project_files_extensions', $form_state->getValue(['om_pssp_upload']))->save();
    \Drupal::messenger()->addStatus(t('Settings updated'));
  }

}
?>
