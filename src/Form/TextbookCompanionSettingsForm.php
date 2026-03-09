<?php

/**
 * @file
 * Contains \Drupal\textbook_companion\Form\TextbookCompanionSettingsForm.
 */

namespace Drupal\textbook_companion\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\ConfigFormBase;


  /**
   * {@inheritdoc}
   */
  class TextbookCompanionSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'textbook_companion.settings_form';
  }
  protected function getEditableConfigNames() {
    return [
      'textbook_comanion.settings',
    ];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
        $config = $this->config('textbook_comanion.settings');
    $form['emails'] = [
      '#type' => 'textfield',
      '#title' => t('(Bcc) Notification emails'),
      '#description' => t('Specify emails id for Bcc option of mail system with comma separated'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('textbook_companion_emails', ''),
    ];
    $form['cc_emails'] = [
      '#type' => 'textfield',
      '#title' => t('(Cc) Notification emails'),
      '#description' => t('Specify emails id for Cc option of mail system with comma separated'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('textbook_companion_cc_emails', ''),
    ];
    $form['from_email'] = [
      '#type' => 'textfield',
      '#title' => t('Outgoing from email address'),
      '#description' => t('Email address to be display in the from field of all outgoing messages'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('textbook_companion_from_email', ''),
    ];
    $form['extensions']['source'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed source file extensions'),
      '#description' => t('A comma separated list WITHOUT SPACE of source file extensions that are permitted to be uploaded on the server'),
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $config->get('textbook_companion_source_extensions', ''),
    ];
    $options = [
      '1' => t('1'),
      '2' => t('2'),
      '3' => t('3'),
    ];
    $form['book_preference_options'] = [
      '#type' => 'radios',
      '#title' => t('Book Preferences'),
      '#options' => $options,
      '#required' => TRUE,
      '#description' => t('Set number book preference to be allowed'),
      '#default_value' => $config->get('textbook_companion_book_preferences', ''),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    // return $form;
        return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
       parent::validateForm($form, $form_state);
    return;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
       parent::validateForm($form, $form_state);
$this->configFactory()->getEditable('textbook_companion.settings')
    ->set('textbook_companion_emails', $form_state->getValue(['emails']))
    ->set('textbook_companion_cc_emails', $form_state->getValue(['cc_emails']))
    ->set('textbook_companion_from_email', $form_state->getValue(['from_email']))
    ->set('textbook_companion_source_extensions', $form_state->getValue(['source']))
    ->set('textbook_companion_book_preferences', $form_state->getValue(['book_preference_options']))
    ->save();
    // drupal_set_message(t('Settings updated'), 'status');
        \Drupal::messenger()->addMessage($this->t('Settings updated'), 'status');
  }

}
?>
