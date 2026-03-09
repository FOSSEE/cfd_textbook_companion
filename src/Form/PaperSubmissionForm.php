<?php

/**
 * @file
 * Contains \Drupal\textbook_companion\Form\PaperSubmissionForm.
 */

namespace Drupal\textbook_companion\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class PaperSubmissionForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'paper_submission_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state, $proposal_id = NULL) {
    $user = \Drupal::currentUser();
    $proposal_id = arg(2);

    /* get current proposal */

    /*$preference4_q = db_query("SELECT * FROM {textbook_companion_paper} WHERE proposal_id=".$proposal_id);*/

    $query = db_select('textbook_companion_paper');
    $query->fields('textbook_companion_paper');
    $query->condition('proposal_id', $proposal_id);
    $preference4_q = $query->execute();

    $form1 = 0;
    $form2 = 0;
    $form3 = 0;
    $form4 = 0;
    if ($data = $preference4_q->fetchObject()) {
      $form1 = $data->internship_form;
      $form2 = $data->copyright_form;
      $form3 = $data->undertaking_form;
      $form4 = $data->reciept_form;
    }
    else {
      $query = "Insert into {textbook_companion_paper} (proposal_id) values (:proposal_id)";
      $args = [":proposal_id" => $proposal_id];
      $result = db_query($query, $args, ['return' => Database::RETURN_INSERT_ID]);
    }
    $form['proposal_id'] = [
      '#type' => 'hidden',
      '#default_value' => $proposal_id,
    ];
    $form['internshipform'] = [
      '#type' => 'checkbox',
      '#title' => t('Recieved Internship Application'),
      '#description' => t('Check if the Internship Application has been recieved.'),
      '#default_value' => $form1,
    ];
    $form['copyrighttransferform'] = [
      '#type' => 'checkbox',
      '#title' => t('Recieved Copyright Transfer Form'),
      '#description' => t('Check if the Copyright Transfer Form has been recieved.'),
      '#default_value' => $form2,
    ];
    $form['undertakingform'] = [
      '#type' => 'checkbox',
      '#title' => t('Recieved Undertaking Form'),
      '#description' => t('Check if the Undertaking Form has been recieved.'),
      '#default_value' => $form3,
    ];
    $form['recieptform'] = [
      '#type' => 'checkbox',
      '#title' => t('Recieved Reciept Form'),
      '#description' => t('Check if the Reciept Form has been recieved.'),
      '#default_value' => $form4,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Send Email'),
    ];
    $form['cancel'] = [
      '#type' => 'markup',
      '#value' => l(t('Cancel'), 'manage_proposal/all'),
    ];
    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {

    /*$query ="UPDATE {textbook_companion_paper} SET internship_form = ".$form_state['values']['internshipform'].", copyright_form = ".$form_state['values']['copyrighttransferform'].", undertaking_form= ".$form_state['values']['undertakingform'].", reciept_form= ".$form_state['values']['recieptform']." WHERE proposal_id = ".$form_state['values']['proposal_id'];
		db_query($query);*/

    $query = db_update('textbook_companion_paper');
    $query->fields([
      'internship_form' => $form_state[ values ][ internshipform ],
      'copyright_form' => $form_state[ values ][ copyrighttransferform ],
      'undertaking_form' => $form_state[ values ][ undertakingform ],
      'reciept_form' => $form_state[ values ][ recieptform ],
    ]);
    $query->condition('proposal_id', $form_state->getValue(['proposal_id']));
    $num_updated = $query->execute();

    /************************************************
		Check For the Internship Form is checked or not
		************************************************/
    if ($form_state->getValue(['internshipform']) == 1) {
      /* sending email */
      $book_user = user_load($proposal_data->uid);
      $param['proposal_completed']['proposal_id'] = $proposal_id;
      $param['proposal_completed']['user_id'] = $proposal_data->uid;
      $email_to = $book_user->mail;
      if (!drupal_mail('textbook_companion', 'internship_form', $email_to, language_default(), $param, variable_get('textbook_companion_from_email', NULL), TRUE)) {
        drupal_set_message('Error sending email message.', 'error');
      }
      drupal_set_message('Internship Form for Book proposal has been recieved. User has been notified .', 'status');
    }
    else {
      if (!drupal_mail('textbook_companion', 'internship_form_not', $email_to, language_default(), $param, variable_get('textbook_companion_from_email', NULL), TRUE)) {
        drupal_set_message('Error sending email message.', 'error');
      }
      drupal_set_message('Internship Form for Book proposal has not been recieved. User has been notified .', 'status');
    }

    /************************************************
		Check For the Copyright Form is checked or not
		************************************************/

    if ($form_state->getValue(['copyrighttransferform']) == 1) {
      /* sending email */
      $book_user = user_load($proposal_data->uid);
      $param['proposal_completed']['proposal_id'] = $proposal_id;
      $param['proposal_completed']['user_id'] = $proposal_data->uid;
      $email_to = $book_user->mail;
      if (!drupal_mail('textbook_companion', 'copyrighttransfer_form', $email_to, language_default(), $param, variable_get('textbook_companion_from_email', NULL), TRUE)) {
        drupal_set_message('Error sending email message.', 'error');
      }
      drupal_set_message('Copyright Form for Book proposal has been recieved. User has been notified .', 'status');
    }
    else {
      if (!drupal_mail('textbook_companion', 'copyrighttransfer_form_not', $email_to, language_default(), $param, variable_get('textbook_companion_from_email', NULL), TRUE)) {
        drupal_set_message('Error sending email message.', 'error');
      }
      drupal_set_message('Copyright Transfer Form for Book proposal has not been recieved. User has been notified .', 'status');
    }

    /************************************************
		Check For the Undertaking Form is checked or not
		************************************************/

    if ($form_state->getValue(['undertakingform']) == 1) {
      /* sending email */
      $book_user = user_load($proposal_data->uid);
      $param['proposal_completed']['proposal_id'] = $proposal_id;
      $param['proposal_completed']['user_id'] = $proposal_data->uid;
      $email_to = $book_user->mail;
      if (!drupal_mail('textbook_companion', 'undertakingform_form', $email_to, language_default(), $param, variable_get('textbook_companion_from_email', NULL), TRUE)) {
        drupal_set_message('Error sending email message.', 'error');
      }
      drupal_set_message('Undertaking Form for Book proposal has been recieved. User has been notified .', 'status');
    }
    else {
      if (!drupal_mail('textbook_companion', 'undertakingform_form_not', $email_to, language_default(), $param, variable_get('textbook_companion_from_email', NULL), TRUE)) {
        drupal_set_message('Error sending email message.', 'error');
      }
      drupal_set_message('Undertaking Form for Book proposal has not been recieved. User has been notified .', 'status');
    }

    drupal_set_message(t('Proposal Updated'), 'status');
  }

}
?>
