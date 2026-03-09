<?php

/**
 * @file
 * Contains \Drupal\textbook_companion\Form\ChequeStatusForm.
 */

namespace Drupal\textbook_companion\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class ChequeStatusForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cheque_status_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state, $proposal_id = NULL) {
    $user = \Drupal::currentUser();

    /* get current proposal */
    $proposal_id = arg(2);

    /*$proposal_q = db_query("SELECT * FROM {textbook_companion_proposal} WHERE id =".$proposal_id);*/
    $query = db_select('textbook_companion_proposal');
    $query->fields('textbook_companion_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();

    /*$proposal_q1 = db_query("SELECT * FROM {textbook_companion_cheque} WHERE proposal_id =".$proposal_id);*/
    $query = db_select('textbook_companion_cheque');
    $query->fields('textbook_companion_cheque');
    $query->condition('proposal_id', $proposal_id);
    $proposal_q1 = $query->execute();


    $proposal_data1 = $proposal_q1->fetchObject();
    if (!$proposal_data = $proposal_q->fetchObject()) {
      drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
      //drupal_goto('manage_proposal');
      return;
    }
    $form['proposal_id'] = [
      '#type' => 'hidden',
      '#default_value' => $proposal_id,
    ];

    /*$empty = db_query("SELECT * FROM {textbook_companion_proposal} WHERE id = ".$proposal_id);*/
    $query = db_select('textbook_companion_proposal');
    $query->fields('textbook_companion_proposal');
    $query->condition('id', $proposal_id);
    $empty = $query->execute();

    if (!$empty) {
      /*$prop =db_query("insert into {textbook_companion_cheque} (proposal_id) values(%d)",$proposal_id);*/

      $query = "insert into {textbook_companion_cheque} (proposal_id) values (:proposal_id)";
      $args = [":proposal_id" => $proposal_id];
      $result = db_query($query, $args, ['return' => Database::RETURN_INSERT_ID]);
    }
    $form['candidate_detail'] = [
      '#type' => 'fieldset',
      '#title' => t('Candidate Details'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#attributes' => [
        'id' => 'candidate_detail'
        ],
    ];
    $form['candidate_detail']['full_name'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->full_name,
      '#title' => t('Contributor Name'),
    ];
    $form['candidate_detail']['email'] = [
      '#type' => 'item',
      '#markup' => user_load($proposal_data->uid)->mail,
      '#title' => t('Email'),
    ];
    $form['candidate_detail']['mobile'] = [
      '#type' => 'item',
      '#markup' => $proposal_data->mobile,
      '#title' => t('Mobile'),
    ];
    $form['candidate_detail']['alt_mobile'] = [
      '#type' => 'item',
      '#markup' => $proposal_data1->alt_mobno,
      '#title' => t('Alternate Mobile No.'),
    ];
    /*$form_q=db_query("SELECT * FROM {textbook_companion_paper} WHERE proposal_id =".$proposal_id);
	$form_data=db_fetch_object($form_q);*/

    $query = db_select('textbook_companion_paper');
    $query->fields('textbook_companion_paper');
    $query->condition('proposal_id', $proposal_id);
    $result = $query->execute();
    $form_data = $result->fetchObject();

    /* get book preference */
    $preference_html = '<ul>';

    /*$preference_q = db_query("SELECT * FROM {textbook_companion_preference} WHERE proposal_id = %d ORDER BY pref_number ASC", $proposal_id);*/
    $query = db_select('textbook_companion_preference');
    $query->fields('textbook_companion_preference');
    $query->condition('proposal_id', $proposal_id);
    $query->orderBy('pref_number', 'ASC');
    $preference_q = $query->execute();

    while ($preference_data = $preference_q->fetchObject()) {
      if ($preference_data->approval_status == 1) {
        $preference_html .= '<li><strong>' . $preference_data->book . ' (Written by ' . $preference_data->author . ')  - Approved Book</strong></li>';
      }
      else {
        $preference_html .= '<li>' . $preference_data->book . ' (Written by ' . $preference_data->author . ')</li>';
      }
    }
    $preference_html .= '</ul>';
    $form['book_preference_f'] = [
      '#type' => 'fieldset',
      '#title' => t('Book Preferences/Application Status'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#attributes' => [
        'id' => 'book_preference_f'
        ],
    ];
    $form['book_preference_f']['book_preference'] = [
      '#type' => 'item',
      '#markup' => $preference_html,
      '#title' => t('Book Preferences'),
    ];

    /*$chq_q=db_query("SELECT * FROM {textbook_companion_cheque} WHERE proposal_id = %d", $proposal_id);
	$chq_data=db_fetch_object($chq_q);*/
    $query = db_select('textbook_companion_cheque');
    $query->fields('textbook_companion_cheque');
    $query->condition('proposal_id', $proposal_id);
    $result = $query->execute();
    $chq_data = $result->fetchObject();

    $form_html .= '<ul>';
    if ($form_data->internship_form) {
      $form_html .= '<li><strong>Internship Application </strong> Form Submitted</li>';
    }
    else {
      $form_html .= '<li><strong>Internship Application </strong> Form Not Submitted </li>';
    }
    if ($form_data->copyright_form) {
      $form_html .= '<li><strong>Copyright Application </strong> Form Submitted</li>';
    }
    else {
      $form_html .= '<li><strong>Copyright Application</strong> Form Not Submitted </li>';
    }
    if ($form_data->undertaking_form) {
      $form_html .= '<li><strong>Undertaking Application </strong> Form Submitted</li>';
    }
    else {
      $form_html .= '<li><strong>Undertaking Application</strong> Form Not Submitted </li>';
    }
    $form_html .= '</ul>';
    $form['book_preference_f']['formsubmit'] = [
      '#type' => 'item',
      '#markup' => $form_html,
      '#title' => t('Application Form Status'),
    ];
    $form['stu_cheque_details'] = [
      '#type' => 'fieldset',
      '#title' => t('Student Cheque Details'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#attributes' => [
        'id' => 'stu_cheque_details'
        ],
    ];
    $form['tea_cheque_details'] = [
      '#type' => 'fieldset',
      '#title' => t('Teacher Cheque Details'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#attributes' => [
        'id' => 'tea_cheque_details'
        ],
    ];
    $form['perm_cheque_address'] = [
      '#type' => 'fieldset',
      '#title' => t('Permanent Address'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#attributes' => [
        'id' => 'perm_cheque_address'
        ],
    ];
    $form['temp_cheque_address'] = [
      '#type' => 'fieldset',
      '#title' => t('Temporary Address'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#attributes' => [
        'id' => 'temp_cheque_address'
        ],
    ];
    $form['cheque_delivery'] = [
      '#type' => 'fieldset',
      '#title' => t('Cheque Delivery'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#attributes' => [
        'id' => 'cheque_delivery'
        ],
    ];
    $form['commentf'] = [
      '#type' => 'fieldset',
      '#title' => t('Remark'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#attributes' => [
        'id' => 'commentf'
        ],
    ];
    /*$chq4_q = db_query("SELECT * FROM {textbook_companion_cheque} WHERE proposal_id=".$proposal_id);*/
    $query = db_select('textbook_companion_cheque');
    $query->fields('textbook_companion_cheque');
    $query->condition('proposal_id', $proposal_id);
    $chq4_q = $query->execute();

    $chq1 = '';
    $chq2 = '';
    $chq3 = '';
    $chq4 = '';
    $chq5 = '';
    $chq6 = '';
    $chq7 = '';
    $chq8 = '';
    $chq9 = '';
    $chq10 = '';
    $chq11 = '';
    $chq12 = '';
    $chq13 = '';
    $chq14 = '';
    $chq15 = '';
    $chq16 = '';
    $chq17 = '';
    if ($chqe = $chq4_q->fetchObject()) {
      $chq1 = $chqe->cheque_no;
      $chq2 = $chqe->address;
      $chq3 = $chqe->cheque_amt;
      $chq4 = $chqe->cheque_sent;
      $chq5 = $chqe->cheque_cleared;
      $chq6 = $chqe->perm_chq_address2;
      $chq7 = $chqe->perm_city;
      $chq8 = $chqe->perm_state;
      $chq9 = $chqe->perm_pincode;
      $chq10 = $chqe->temp_chq_address;
      $chq11 = $chqe->temp_chq_address2;
      $chq12 = $chqe->temp_city;
      $chq13 = $chqe->temp_state;
      $chq14 = $chqe->temp_pincode;
      $chq15 = $chqe->commentf;
      $chq16 = $chqe->t_cheque_amt;
      $chq17 = $chqe->t_cheque_no;
      $form['stu_cheque_details']['cheque_no'] = [
        '#type' => 'textfield',
        '#default_value' => $chq1,
        '#title' => t('Cheque No'),
        '#size' => 54,
      ];
      $form['tea_cheque_details']['cheque_no_t'] = [
        '#type' => 'textfield',
        '#default_value' => $chq17,
        '#title' => t('Cheque No'),
        '#size' => 54,
      ];
      $form['perm_cheque_address']['chq_address'] = [
        '#type' => 'textarea',
        '#default_value' => $chq2,
        '#title' => t('Address Street 1'),
      ];
      $form['perm_cheque_address']['chq_address']['#attributes']['readonly'] = 'readonly';
      $form['perm_cheque_address']['perm_city'] = [
        '#type' => 'textfield',
        '#default_value' => $chq7,
        '#title' => t('City'),
        '#size' => 35,
      ];
      $form['perm_cheque_address']['perm_city']['#attributes']['readonly'] = 'readonly';
      $form['perm_cheque_address']['perm_state'] = [
        '#type' => 'textfield',
        '#default_value' => $chq8,
        '#title' => t('State'),
        '#size' => 35,
      ];
      $form['perm_cheque_address']['perm_state']['#attributes']['readonly'] = 'readonly';
      $form['perm_cheque_address']['perm_pincode'] = [
        '#type' => 'textfield',
        '#default_value' => $chq9,
        '#title' => t('Zip code'),
        '#size' => 35,
      ];
      $form['perm_cheque_address']['perm_pincode']['#attributes']['readonly'] = 'readonly';
      $form['stu_cheque_details']['cheq_amt'] = [
        '#type' => 'textfield',
        '#default_value' => $chq3,
        '#title' => t('Cheque Amount'),
        '#size' => 54,
      ];
      $form['tea_cheque_details']['cheq_amt_t'] = [
        '#type' => 'textfield',
        '#default_value' => $chq17,
        '#title' => t('Cheque Amount'),
        '#size' => 54,
      ];
      $form['temp_cheque_address']['temp_chq_address'] = [
        '#type' => 'textarea',
        '#default_value' => $chq10,
        '#title' => t('Address Street 1'),
      ];
      $form['temp_cheque_address']['temp_chq_address']['#attributes']['readonly'] = 'readonly';
      $form['temp_cheque_address']['temp_city'] = [
        '#type' => 'textfield',
        '#default_value' => $chq12,
        '#title' => t('City'),
        '#size' => 35,
      ];
      $form['temp_cheque_address']['temp_city']['#attributes']['readonly'] = 'readonly';
      $form['temp_cheque_address']['temp_state'] = [
        '#type' => 'textfield',
        '#default_value' => $chq13,
        '#title' => t('State'),
        '#size' => 35,
      ];
      $form['temp_cheque_address']['temp_state']['#attributes']['readonly'] = 'readonly';
      $form['temp_cheque_address']['temp_pincode'] = [
        '#type' => 'textfield',
        '#default_value' => $chq14,
        '#title' => t('Zipcode'),
        '#size' => 35,
      ];
      $form['temp_cheque_address']['temp_pincode']['#attributes']['readonly'] = 'readonly';
    }
    else {
      $form['stu_cheque_details']['cheque_no'] = [
        '#type' => 'textfield',
        '#default_value' => $chq1,
        '#title' => t('Cheque No'),
      ];
      $form['tea_cheque_details']['cheque_no_t'] = [
        '#type' => 'textfield',
        '#default_value' => $chq16,
        '#title' => t('Cheque No'),
      ];
      $form['perm_cheque_address']['chq_address'] = [
        '#type' => 'textarea',
        '#default_value' => $chq2,
        '#title' => t('Address Street 1'),
      ];
      $form['perm_cheque_address']['perm_city'] = [
        '#type' => 'textfield',
        '#default_value' => $chq7,
        '#title' => t('City'),
        '#size' => 35,
      ];
      $form['perm_cheque_address']['perm_state'] = [
        '#type' => 'textfield',
        '#default_value' => $chq8,
        '#title' => t('State'),
        '#size' => 35,
      ];
      $form['perm_cheque_address']['perm_pincode'] = [
        '#type' => 'textfield',
        '#default_value' => $chq9,
        '#title' => t('Zip code'),
        '#size' => 35,
      ];
      $form['perm_cheque_address']['same_address'] = [
        '#type' => 'checkbox',
        '#title' => t('Same As Permanent Address'),
        '#attributes' => [
          'onclick' => 'copy_address()'
          ],
      ];
      $form['stu_cheque_details']['cheq_amt'] = [
        '#type' => 'textfield',
        '#default_value' => $chq3,
        '#title' => t('Cheque Amount'),
      ];
      $form['tea_cheque_details']['cheq_amt'] = [
        '#type' => 'textfield',
        '#default_value' => $chq17,
        '#title' => t('Cheque Amount'),
      ];
      $form['temp_cheque_address']['temp_chq_address'] = [
        '#type' => 'textarea',
        '#default_value' => $chq10,
        '#title' => t('Address Street 1'),
      ];
      $form['temp_cheque_address']['temp_city'] = [
        '#type' => 'textfield',
        '#default_value' => $chq12,
        '#title' => t('City'),
        '#size' => 35,
      ];
      $form['temp_cheque_address']['temp_state'] = [
        '#type' => 'textfield',
        '#default_value' => $chq13,
        '#title' => t('State'),
        '#size' => 35,
      ];
      $form['temp_cheque_address']['temp_pincode'] = [
        '#type' => 'textfield',
        '#default_value' => $chq14,
        '#title' => t('Zip code'),
        '#size' => 35,
      ];
      $form['temp_cheque_address']['same_address'] = [
        '#type' => 'checkbox',
        '#title' => t('Same As Permanent Address'),
      ];
      $form['temp_cheque_address']['same_address'] = [
        '#type' => 'checkbox',
        '#title' => t('Same As Permanent Address'),
        '#attributes' => [
          'onclick' => 'copy_address()'
          ],
      ];
    }
    $form['cheque_delivery']['cheque_sent'] = [
      '#type' => 'checkbox',
      '#title' => t('Cheque Sent'),
      '#default_value' => $chq4,
      '#description' => t('Check if the Cheque has been sent to the user.'),
      '#attributes' => [
        'id' => 'cheque_sent'
        ],
    ];
    $form['cheque_delivery']['cheque_cleared'] = [
      '#type' => 'checkbox',
      '#title' => t('Cheque Cleared'),
      '#default_value' => $chq5,
      '#description' => t('Check if the Cheque has been <strong>Realised</strong> to the User Account.'),
      '#attributes' => [
        'id' => 'cheque_cleared'
        ],
    ];
    $form['commentf']['comment_cheque'] = [
      '#type' => 'textarea',
      '#size' => 35,
      '#attributes' => [
        'id' => 'comment'
        ],
      '#default_value' => $chq15,
    ];
    $form['proposal_id'] = [
      '#type' => 'hidden',
      '#value' => $proposal_id,
    ];

    /*$preference1_p = db_query("SELECT * FROM {textbook_companion_paper} WHERE proposal_id = %d ORDER BY id ASC", $proposal_id); */

    $query = db_select('textbook_companion_paper');
    $query->fields('textbook_companion_paper');
    $query->condition('proposal_id', $proposal_id);
    $query->orderBy('id', 'ASC');
    $preference1_p = $query->execute();

    if (!($proposal_data1 = $preference1_p->fetchObject())) {
      drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
      //drupal_goto('manage_proposal');
      return;
    }
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    $form['cancel'] = [
      '#type' => 'markup',
      '#value' => l(t('Cancel'), 'manage_proposal/all'),
    ];
    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $proposal_id = arg(2);


    /*$query ="UPDATE {textbook_companion_cheque} SET 
	cheque_no = ".$form_state['values']['cheque_no'].",
	cheque_amt = ".$form_state['values']['cheq_amt'].",
	alt_mobno = '".$form_state['values']['mobileno2']."', 
	address = '".$form_state['values']['chq_address']."',  
	perm_city = '".$form_state['values']['perm_city']."', 
	perm_state = '".$form_state['values']['perm_state']."', 
	perm_pincode = '".$form_state['values']['perm_pincode']."', 
	temp_chq_address = '".$form_state['values']['temp_chq_address']."', 
	temp_city = '".$form_state['values']['temp_city']."', 
	temp_state = '".$form_state['values']['temp_state']."', 
	temp_pincode = '".$form_state['values']['temp_pincode']."',
	commentf = '".$form_state['values']['comment_cheque']."', 
	t_cheque_no = ".$form_state['values']['cheque_no_t'].",
	t_cheque_amt = ".$form_state['values']['cheq_amt_t']."
	WHERE proposal_id = ".$proposal_id;
	
	db_query($query);*/

    $query = db_update('textbook_companion_cheque');
    $query->fields([
      'cheque_no' => $form_state[ values ][ cheque_no ],
      'cheque_amt' => $form_state[ values ][ cheq_amt ],
      'alt_mobno' => $form_state[ values ][ mobileno2 ],
      'address' => $form_state[ values ][ chq_address ],
      'perm_city' => $form_state[ values ][ perm_city ],
      'perm_state' => $form_state[ values ][ perm_state ],
      'perm_pincode' => $form_state[ values ][ perm_pincode ],
      'temp_chq_address' => $form_state[ values ][ temp_chq_address ],
      'temp_city' => $form_state[ values ][ temp_city ],
      'temp_state' => $form_state[ values ][ temp_state ],
      'temp_pincode' => $form_state[ values ][ temp_pincode ],
      'commentf' => $form_state[ values ][ comment_cheque ],
      't_cheque_no' => $form_state[ values ][ cheque_no_t ],
      't_cheque_amt' => $form_state[ values ][ cheq_amt_t ],
    ]);
    $query->condition('proposal_id', $proposal_id);
    $num_updated = $query->execute();

    if ($form_state->getValue(['cheque_sent']) == 1) {
      /* sending email */
      /*$query ="UPDATE {textbook_companion_cheque} SET cheque_sent = ".$form_state['values']['cheque_sent']." WHERE proposal_id = ".$proposal_id;
		db_query($query);*/

      $query = db_update('textbook_companion_cheque');
      $query->fields(['cheque_sent' => $form_state[ values ][ cheque_sent ]]);
      $query->condition('proposal_id', $proposal_id);
      $num_updated = $query->execute();

      $book_user = user_load($proposal_data->uid);
      $param['proposal_completed']['proposal_id'] = $proposal_id;
      $param['proposal_completed']['user_id'] = $proposal_data->uid;
      $email_to = $book_user->mail;
      if (!drupal_mail('textbook_companion', 'cheque_sent', $email_to, language_default(), $param, variable_get('textbook_companion_from_email', NULL), TRUE)) {
        drupal_set_message('Error sending email message.', 'error');
      }
      drupal_set_message('Cheque for Book proposal has been Sent. User has been notified .', 'status');
    }


    if ($form_state->getValue(['cheque_cleared']) == 1) {
      /*$query ="UPDATE {textbook_companion_cheque} SET cheque_cleared = ".$form_state['values']['cheque_cleared']." WHERE proposal_id = ".$proposal_id;
		db_query($query);*/

      $query = db_update('textbook_companion_cheque');
      $query->fields([
        'cheque_cleared' => $form_state[ values ][ cheque_cleared ]
        ]);
      $query->condition('proposal_id', $proposal_id);
      $num_updated = $query->execute();

      $curtime = MySQL_NOW();
      echo $curtime;
      drupal_set_message('Cheque Has Been Debited into User Account.', 'status');
      /*$queryc ="UPDATE {textbook_companion_cheque} SET cheque_dispatch_date = NOW() WHERE proposal_id = ".$form_state['values']['proposal_id']."";
		db_query($queryc);*/

      $query = db_update('textbook_companion_cheque');
      $query->fields(['cheque_dispatch_date' => 'NOW']);
      $query->condition('proposal_id', $form_state->getValue(['proposal_id']));
      $num_updated = $query->execute();
    }

    /************************************************
	 Check For the Remark 
	************************************************/
    if ($form_state->getValue(['comment_cheque'])) {
      /* sending email */
      $book_user = user_load($proposal_data->uid);
      $param['proposal_completed']['proposal_id'] = $proposal_id;
      $param['proposal_completed']['user_id'] = $proposal_data->uid;
      $email_to = $book_user->mail;
      if (!drupal_mail('textbook_companion', 'remark', $email_to, language_default(), $param, variable_get('textbook_companion_from_email', NULL), TRUE)) {
        drupal_set_message('Error sending email message.', 'error');
      }
      drupal_set_message(t('Remark Updated. User has been notified'), 'status');
    }
    else {
      if (!drupal_mail('textbook_companion', 'remark_not', $email_to, language_default(), $param, variable_get('textbook_companion_from_email', NULL), TRUE)) {
        drupal_set_message('Error sending email message.', 'error');
      }
      drupal_set_message('No Remarks. User has been notified .', 'status');
    }
  }

}
?>
