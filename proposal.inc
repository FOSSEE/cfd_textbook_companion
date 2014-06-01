<?php

function book_proposal_form($form_state)
{
  global $user;

  if (!$user->uid) {
		drupal_set_message('It is mandatory to login on this website to access the proposal form', 'error');
		return;
  }

  /* check if user has already submitted a proposal */
  $proposal_q = db_query("SELECT * FROM {textbook_companion_proposal} WHERE uid = %d ORDER BY id DESC LIMIT 1", $user->uid);
  if ($proposal_q)
  {
    if ($proposal_data = db_fetch_object($proposal_q))
    {
      switch ($proposal_data->proposal_status)
      {
      case 0:
        drupal_set_message(t('We have already received your proposal. We will get back to you soon.'), 'status');
        drupal_goto('');
        return;
        break;
      case 1:
        drupal_set_message(t('Your proposal has been approved. Please go to ' . l('Code Submission', 'textbook_companion/code') . ' to upload your code'), 'status');
        drupal_goto('');
        return;
        break;
      case 2:
        drupal_set_message(t('Your proposal has been dis-approved. Please create another proposal below.'), 'error');
        break;
      case 3:
        drupal_set_message(t('Congratulations! You have completed your last book proposal. You can create another proposal below.'), 'status');
        break;
      default:
         drupal_set_message(t('Invalid proposal state. Please contact site administrator for further information.'), 'error');
         drupal_goto('');
         return;
         break;
      }
    }
  }

  $form['imp_notice'] = array(
    '#type' => 'item',
    '#value' => '<font color="red"><b>Please fill up this form carefully as the details entered here will be exactly written in the Textbook Companion</b></font>',
  );

  $form['full_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Full Name'),
    '#size' => 30,
    '#maxlength' => 50,
    '#required' => TRUE,
  );
  $form['email_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Email'),
    '#size' => 30,
    '#value' => $user->mail,
    '#disabled' => TRUE,
  );
  $form['mobile'] = array(
    '#type' => 'textfield',
    '#title' => t('Mobile No.'),
    '#size' => 30,
    '#maxlength' => 15,
    '#required' => TRUE,
  );
  $form['how_project'] = array(
    '#type' => 'select',
    '#title' => t('How did you come to know about this project'),
    '#options' => array('Oscad Website' => 'Oscad Website',
                        'Friend' => 'Friend',
                        'Professor/Teacher' => 'Professor/Teacher',
                        'Mailing List' => 'Mailing List',
                        'Poster in my/other college' => 'Poster in my/other college',
                        'Others' => 'Others'),
    '#required' => TRUE,
  );
  $form['course'] = array(
    '#type' => 'textfield',
    '#title' => t('Course'),
    '#size' => 30,
    '#maxlength' => 50,
    '#required' => TRUE,
  );
  $form['branch'] = array(
    '#type' => 'select',
    '#title' => t('Department/Branch'),
    '#options' => array('Electrical Engineering' => 'Electrical Engineering',
                        'Electronics Engineering' => 'Electronics Engineering',
                        'Computer Engineering' => 'Computer Engineering',
                        'Chemical Engineering' => 'Chemical Engineering',
                        'Instrumentation Engineering' => 'Instrumentation Engineering',
                        'Mechanical Engineering' => 'Mechanical Engineering',
                        'Civil Engineering' => 'Civil Engineering',
                        'Physics' => 'Physics',
                        'Mathematics' => 'Mathematics',
                        'Others' => 'Others'),
    '#required' => TRUE,
  );
  $form['university'] = array(
    '#type' => 'textfield',
    '#title' => t('University/Institute'),
    '#size' => 30,
    '#maxlength' => 50,
    '#required' => TRUE,
  );
  $form['faculty'] = array(
    '#type' => 'textfield',
    '#title' => t('College Teacher/Professor'),
    '#size' => 30,
    '#maxlength' => 50,
    '#required' => TRUE,
  );
  $form['reviewer'] = array(
    '#type' => 'textfield',
    '#title' => t('Reviewer'),
    '#size' => 30,
    '#maxlength' => 50,
  );
  $form['completion_date'] = array(
    '#type' => 'textfield',
    '#title' => t('Expected Date of Completion'),
    '#description' => t('Input date format should be DD-MM-YYYY. Eg: 23-03-2011'),
    '#size' => 10,
    '#maxlength' => 10,
  );
  $form['preference1'] = array(
    '#type' => 'fieldset',
    '#title' => t('Book Preference 1'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['preference1']['book1'] = array(
    '#type' => 'textfield',
    '#title' => t('Title of the book'),
    '#size' => 30,
    '#maxlength' => 100,
    '#required' => TRUE,
  );
  $form['preference1']['author1'] = array(
    '#type' => 'textfield',
    '#title' => t('Author Name'),
    '#size' => 30,
    '#maxlength' => 100,
    '#required' => TRUE,
  );
  $form['preference1']['isbn1'] = array(
    '#type' => 'textfield',
    '#title' => t('ISBN No'),
    '#size' => 30,
    '#maxlength' => 25,
    '#required' => TRUE,
  );
  $form['preference1']['publisher1'] = array(
    '#type' => 'textfield',
    '#title' => t('Publisher & Place'),
    '#size' => 30,
    '#maxlength' => 50,
    '#required' => TRUE,
  );
  $form['preference1']['edition1'] = array(
    '#type' => 'textfield',
    '#title' => t('Edition'),
    '#size' => 4,
    '#maxlength' => 2,
    '#required' => TRUE,
  );
  $form['preference1']['year1'] = array(
    '#type' => 'textfield',
    '#title' => t('Year of pulication'),
    '#size' => 4,
    '#maxlength' => 4,
    '#required' => TRUE,
  );
  $form['preference2'] = array(
    '#type' => 'fieldset',
    '#title' => t('Book Preference 2'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['preference2']['book2'] = array(
    '#type' => 'textfield',
    '#title' => t('Title of the book'),
    '#size' => 30,
    '#maxlength' => 100,
    '#required' => TRUE,
  );
  $form['preference2']['author2'] = array(
    '#type' => 'textfield',
    '#title' => t('Author Name'),
    '#size' => 30,
    '#maxlength' => 100,
    '#required' => TRUE,
  );
  $form['preference2']['isbn2'] = array(
    '#type' => 'textfield',
    '#title' => t('ISBN No'),
    '#size' => 30,
    '#maxlength' => 25,
    '#required' => TRUE,
  );
  $form['preference2']['publisher2'] = array(
    '#type' => 'textfield',
    '#title' => t('Publisher & Place'),
    '#size' => 30,
    '#maxlength' => 50,
    '#required' => TRUE,
  );
  $form['preference2']['edition2'] = array(
    '#type' => 'textfield',
    '#title' => t('Edition'),
    '#size' => 4,
    '#maxlength' => 2,
    '#required' => TRUE,
  );
  $form['preference2']['year2'] = array(
    '#type' => 'textfield',
    '#title' => t('Year of pulication'),
    '#size' => 4,
    '#maxlength' => 4,
    '#required' => TRUE,
  );
  $form['preference3'] = array(
    '#type' => 'fieldset',
    '#title' => t('Book Preference 3'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['preference3']['book3'] = array(
    '#type' => 'textfield',
    '#title' => t('Title of the book'),
    '#size' => 30,
    '#maxlength' => 100,
    '#required' => TRUE,
  );
  $form['preference3']['author3'] = array(
    '#type' => 'textfield',
    '#title' => t('Author Name'),
    '#size' => 30,
    '#maxlength' => 100,
    '#required' => TRUE,
  );
  $form['preference3']['isbn3'] = array(
    '#type' => 'textfield',
    '#title' => t('ISBN No'),
    '#size' => 30,
    '#maxlength' => 25,
    '#required' => TRUE,
  );
  $form['preference3']['publisher3'] = array(
    '#type' => 'textfield',
    '#title' => t('Publisher & Place'),
    '#size' => 30,
    '#maxlength' => 50,
    '#required' => TRUE,
  );
  $form['preference3']['edition3'] = array(
    '#type' => 'textfield',
    '#title' => t('Edition'),
    '#size' => 4,
    '#maxlength' => 2,
    '#required' => TRUE,
  );
  $form['preference3']['year3'] = array(
    '#type' => 'textfield',
    '#title' => t('Year of pulication'),
    '#size' => 4,
    '#maxlength' => 4,
    '#required' => TRUE,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit')
  );
  return $form;
}

function book_proposal_form_validate($form, &$form_state)
{
  /* mobile */
  if (!preg_match('/^[0-9\ \+]{0,15}$/', $form_state['values']['mobile']))
    form_set_error('mobile', t('Invalid mobile number'));

  /* date of completion */
  if (!preg_match('/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/', $form_state['values']['completion_date']))
    form_set_error('completion_date', t('Invalid expected date of completion'));

  list($d, $m, $y) = explode('-', $form_state['values']['completion_date']);
  $d = (int)$d; $m = (int)$m; $y = (int)$y;
  if (!checkdate($m, $d, $y))
    form_set_error('completion_date', t('Invalid expected date of completion'));
  if (mktime(0, 0, 0, $m, $d, $y) <= time())
  	form_set_error('completion_date', t('Expected date of completion should be in future'));  

  /* edition */
  if (!preg_match('/^[1-9][0-9]{0,1}$/', $form_state['values']['edition1']))
    form_set_error('edition1', t('Invalid edition for Book Preference 1'));
  if (!preg_match('/^[1-9][0-9]{0,1}$/', $form_state['values']['edition2']))
    form_set_error('edition2', t('Invalid edition for Book Preference 2'));
  if (!preg_match('/^[1-9][0-9]{0,1}$/', $form_state['values']['edition3']))
    form_set_error('edition3', t('Invalid edition for Book Preference 3'));

  /* year of publication */
  if (!preg_match('/^[1-3][0-9][0-9][0-9]$/', $form_state['values']['year1']))
    form_set_error('year1', t('Invalid year of pulication for Book Preference 1'));
  if (!preg_match('/^[1-3][0-9][0-9][0-9]$/', $form_state['values']['year2']))
    form_set_error('year2', t('Invalid year of pulication for Book Preference 2'));
  if (!preg_match('/^[1-3][0-9][0-9][0-9]$/', $form_state['values']['year3']))
    form_set_error('year3', t('Invalid year of pulication for Book Preference 3'));

  /* year of publication */
  $cur_year = date('Y');
  if ((int)$form_state['values']['year1'] > $cur_year)
    form_set_error('year1', t('Year of pulication should be not in the future for Book Preference 1'));
  if ((int)$form_state['values']['year2'] > $cur_year)
    form_set_error('year2', t('Year of pulication should be not in the future for Book Preference 2'));
  if ((int)$form_state['values']['year3'] > $cur_year)
    form_set_error('year3', t('Year of pulication should be not in the future for Book Preference 3'));

  /* isbn */
  if (!preg_match('/^[0-9\-xX]+$/', $form_state['values']['isbn1']))
    form_set_error('isbn1', t('Invalid ISBN for Book Preference 1'));
  if (!preg_match('/^[0-9\-xX]+$/', $form_state['values']['isbn2']))
    form_set_error('isbn2', t('Invalid ISBN for Book Preference 2'));
  if (!preg_match('/^[0-9\-xX]+$/', $form_state['values']['isbn3']))
    form_set_error('isbn3', t('Invalid ISBN for Book Preference 3'));
  return;
}

function book_proposal_form_submit($form, &$form_state)
{
  global $user;

  if (!$user->uid) {
		drupal_set_message('It is mandatory to login on this website to access the proposal form', 'error');
		return;
  }

  /* completion date to timestamp */
  list($d, $m, $y) = explode('-', $form_state['values']['completion_date']);
  $completion_date_timestamp = mktime(0, 0, 0, $m, $d, $y);

  /* inserting the user proposal */
  $result = db_query("INSERT INTO {textbook_companion_proposal}
    (uid, approver_uid, full_name, mobile, how_project, course, branch, university, faculty, reviewer, completion_date, creation_date, approval_date, proposal_status) VALUES
    (%d, %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, %d, %d)",
    $user->uid,
    0,
    ucwords(strtolower($form_state['values']['full_name'])),
    $form_state['values']['mobile'],
    $form_state['values']['how_project'],
    $form_state['values']['course'],
    $form_state['values']['branch'],
    $form_state['values']['university'],
    ucwords(strtolower($form_state['values']['faculty'])),
    ucwords(strtolower($form_state['values']['reviewer'])),
    $completion_date_timestamp,
    time(),
    0,
    0
  );
  if (!$result)
  {
    drupal_set_message(t('Error receiving your proposal. Please try again.'), 'error');
    return;
  }
  /* proposal id */
  $proposal_id = db_last_insert_id('textbook_companion_proposal', 'id');

  /* inserting first book preference */
  if ($form_state['values']['book1'])
  {
    $result = db_query("INSERT INTO {textbook_companion_preference}
      (proposal_id, pref_number, book, author, isbn, publisher, edition, year, category, approval_status) VALUES
      (%d, %d, '%s', '%s', '%s', '%s', '%s', '%s', %d, %d)",
      $proposal_id,
      1,
      ucwords(strtolower($form_state['values']['book1'])),
      ucwords(strtolower($form_state['values']['author1'])),
      $form_state['values']['isbn1'],
      ucwords(strtolower($form_state['values']['publisher1'])),
      $form_state['values']['edition1'],
      $form_state['values']['year1'],
      0,
      0
    );
    if (!$result)
    {
      drupal_set_message(t('Error receiving your first book preference.'), 'error');
    }
  }

  /* inserting second book preference */
  if ($form_state['values']['book2'])
  {
    $result = db_query("INSERT INTO {textbook_companion_preference}
      (proposal_id, pref_number, book, author, isbn, publisher, edition, year, category, approval_status) VALUES
      (%d, %d, '%s', '%s', '%s', '%s', '%s', '%s', %d, %d)",
      $proposal_id,
      2,
      ucwords(strtolower($form_state['values']['book2'])),
      ucwords(strtolower($form_state['values']['author2'])),
      $form_state['values']['isbn2'],
      ucwords(strtolower($form_state['values']['publisher2'])),
      $form_state['values']['edition2'],
      $form_state['values']['year2'],
      0,
      0
    );
    if (!$result)
    {
      drupal_set_message(t('Error receiving your second book preference.'), 'error');
    }
  }

  /* inserting third book preference */
  if ($form_state['values']['book3'])
  {
    $result = db_query("INSERT INTO {textbook_companion_preference}
      (proposal_id, pref_number, book, author, isbn, publisher, edition, year, category, approval_status) VALUES
      (%d, %d, '%s', '%s', '%s', '%s', '%s', '%s', %d, %d)",
      $proposal_id,
      3,
      ucwords(strtolower($form_state['values']['book3'])),
      ucwords(strtolower($form_state['values']['author3'])),
      $form_state['values']['isbn3'],
      ucwords(strtolower($form_state['values']['publisher3'])),
      $form_state['values']['edition3'],
      $form_state['values']['year3'],
      0,
      0
    );
    if (!$result)
    {
      drupal_set_message(t('Error receiving your third book preference.'), 'error');
    }
  }

  /* sending email */
  $email_to = $user->mail;
  $param['proposal_received']['proposal_id'] = $proposal_id;
  $param['proposal_received']['user_id'] = $user->uid;
  if (!drupal_mail('textbook_companion', 'proposal_received', $email_to , language_default(), $param, variable_get('textbook_companion_from_email', NULL), TRUE))
    drupal_set_message('Error sending email message.', 'error');

  drupal_set_message(t('We have received you book proposal. We will get back to you soon.'), 'status');
  drupal_goto('');
}
