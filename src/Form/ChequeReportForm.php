<?php

/**
 * @file
 * Contains \Drupal\textbook_companion\Form\ChequeReportForm.
 */

namespace Drupal\textbook_companion\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class ChequeReportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cheque_report_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    /*$search_qx = db_query("SELECT * FROM textbook_companion_proposal p,textbook_companion_cheque c WHERE c.address_con = 'Submitted' AND (p.id = c.proposal_id)");*/

    $query = db_select('textbook_companion_proposal', 'p');
    $query->join('textbook_companion_cheque', 'c', 'p.id = c.proposal_id');
    $query->fields('p', ['textbook_companion_proposal']);
    $query->fields('c', ['textbook_companion_cheque']);
    $query->condition('c.address_con', 'Submitted');
    $search_qx = $query->execute();

    while ($search_datax = $search_qx->fetchObject()) {
      $result = [
        $search_datax->full_name,
        $search_datax->address_con,
        $search_datax->cheque_no,
        $search_datax->cheque_dispatch_date,
      ];
    }
    if (!$result) {
      die('Couldn\'t fetch records');
    }
    $num_fields = count($result);
    $headers = [];
    for ($i = 0; $i < $num_fields; $i++) {
      $headers[] = mysql_field_name($result, $i);
    }

    $row = [];
    $fp = fopen('php://output', 'w');
    $search_header = [
      'Name Of The Student',
      'Application Form Status',
      'Cheque No',
      'Cheque Clearance Date',
    ];
    fputcsv($fp, $search_header);
    if ($fp && $result) {
      header('Content-Type: text/csv');
      header('Content-Disposition: attachment; filename="Report.csv"');
      header('Pragma: no-cache');
      header('Expires: 0');
      fputcsv($fp, $headers);
      while ($row = mysql_fetch_row($result)) {
        fputcsv($fp, array_values($row));
      }
      die;
    }
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
  }
}
?>
