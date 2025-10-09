<?php
namespace Drupal\textbook_companion\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Link;

class TextbookCompanionRunForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'textbook_companion_run_form';
  }

  /**
   * Helper function to generate the Chapter Download Link markup.
   *
   * @param int $chapter_id
   * The ID of the currently selected chapter.
   * @return string
   * The HTML markup for the download link.
   */
  private function getChapterDownloadLinkMarkup($chapter_id) {
    if (!$chapter_id) {
        return '';
    }

    $link = Link::fromTextAndUrl(
        $this->t('Download Chapter'),
        Url::fromRoute('textbook_companion.download_chapter', ['chapter_id' => $chapter_id])
    )->toString();

    return $link;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // ... (Your existing code for getting default values is fine) ...

    $url_book_pref_id = \Drupal::request()->attributes->get('book_pref_id') ?? 0;
    $category_default_value = 0;

    if ($url_book_pref_id) {
      $query = \Drupal::database()->select('textbook_companion_preference', 't');
      $query->fields('t', ['category']);
      $query->condition('id', $url_book_pref_id);
      $result = $query->execute()->fetchObject();
      $category_default_value = $result ? $result->category : 0;
    }

    $selected_book = $form_state->getValue('book') ?? $url_book_pref_id;

    // ---------------------------
    // BOOK SELECT FIELD
    // ---------------------------
    $form['book'] = [
      '#type' => 'select',
      '#title' => $this->t('Title of the book'),
      '#options' => $this->_list_of_books($category_default_value),
      '#default_value' => $selected_book,
      '#ajax' => [
        'callback' => '::ajax_book_changed_callback',
        'wrapper' => 'book-dependents-wrapper',
        'event' => 'change',
      ],
    ];

    // This wrapper contains everything that depends on the selected book.
    $form['book_dependents_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'book-dependents-wrapper'],
    ];

    $book_info = $selected_book ? $this->_html_book_info($selected_book) : '';
    $form['book_dependents_wrapper']['book_details_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'ajax-book-details'],
      '#markup' => $book_info,
    ];

    $form['book_dependents_wrapper']['download_book'] = [
      '#type' => 'item',
      '#markup' => Link::fromTextAndUrl(
        $this->t('Download Book'),
        Url::fromRoute('textbook_companion.download_book', ['book_id' => $selected_book])
      )->toString() . ' ' . $this->t('(Download the OpenFOAM codes for all the solved examples)'),
      '#states' => [ 'invisible' => [':input[name="book"]' => ['value' => 0]]],
    ];

    // // ---------------------------
    // // Chapter select and its download link
    // // ---------------------------
    $chapter_id = $form_state->getValue('chapter') ?? 0;
    $form['book_dependents_wrapper']['chapter'] = [
      '#type' => 'select',
      '#title' => $this->t('Title of the chapter'),
      '#options' => $this->_list_of_chapters($selected_book),
      '#default_value' => $chapter_id,
      '#ajax' => [
        'callback' => '::ajax_chapter_changed_callback',
        'wrapper' => 'example-wrapper', // Target the wrapper for the examples.
      ],
      '#states' => [ 'invisible' => [':input[name="book"]' => ['value' => 0]]],
    ];

    // ✅ FIX: The chapter download link is now wrapped in its own specific container
    // so we can update it precisely in the AJAX callback.
    $form['book_dependents_wrapper']['chapter_download_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'chapter-download-link-wrapper'],
        // The link element is now inside the wrapper
        'chapter_download_item' => [
            '#type' => 'item',
            '#markup' => $this->getChapterDownloadLinkMarkup($chapter_id),
            '#states' => ['invisible' => [':input[name="chapter"]' => ['value' => 0]]],
        ],
        // The states array needs to be on the container now if you want to hide the whole thing
        '#states' => ['invisible' => [':input[name="chapter"]' => ['value' => 0]]],
    ];


    // ---------------------------
    // Example select and its download link

    // ... (code before example selection)

    $selected_example = $form_state->getValue('examples') ?? 0;

    // ---------------------------
    // Example Details Wrapper (Link + File Table)
    // ---------------------------
    // This wrapper is the target for the example changed AJAX callback.
   
    // ---------------------------
    // This wrapper contains everything that depends on the selected chapter.
    $form['example_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'example-wrapper'],
    ];

    $form['example_wrapper']['examples'] = [
      '#type' => 'select',
      '#title' => $this->t('Example No. (Caption):'),
      '#options' => $this->_list_of_examples($chapter_id),
      '#ajax' => [
        'callback' => '::ajax_example_changed_callback',
        // ✅ UPDATED: Point to the new, more specific wrapper for the example link.
        'wrapper' => 'download-example-link-wrapper',
      ],
      '#states' => ['invisible' => [':input[name="chapter"]' => ['value' => 0]]],
    ];

    // ✅ NEW: A dedicated wrapper just for the example download link.
    $form['example_wrapper']['download_example_link_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'download-example-link-wrapper'],
    ];
    
    $selected_example = $form_state->getValue('examples') ?? 0;
    $form['example_wrapper']['download_example_link_wrapper']['example_download'] = [
        '#type' => 'item',
        '#markup' => Link::fromTextAndUrl(
            $this->t('Download OpenFOAM code for the example' ),
            Url::fromRoute('textbook_companion.download_example', ['example_id' => $selected_example])
        )->toString(),
        '#states' => ['invisible' => [':input[name="examples"]' => ['value' => 0]]],
    ];


    // //  code for download the file and display the filename and type
    
// ... (Your code up to the example select field) ...
//     $selected_example = $form_state->getValue('examples') ?? 0;

//     // ---------------------------
//     // Example Details Wrapper (Link + File Table)
//     // ---------------------------
//     // This wrapper is the target for the example changed AJAX callback.
   
//     // ---------------------------
//     // This wrapper contains everything that depends on the selected chapter.
//     $form['example_wrapper'] = [
//       '#type' => 'container',
//       '#attributes' => ['id' => 'example-wrapper'],
//     ];

//     $form['example_wrapper']['examples'] = [
//       '#type' => 'select',
//       '#title' => $this->t('Example No. (Caption):'),
//       '#options' => $this->_list_of_examples($chapter_id),
//       '#ajax' => [
//         'callback' => '::ajax_example_changed_callback',
//         // ✅ UPDATED: Point to the new, more specific wrapper for the example link and table.
//         'wrapper' => 'download-example-link-wrapper',
//       ],
//       '#states' => ['invisible' => [':input[name="chapter"]' => ['value' => 0]]],
//     ];

//     // ✅ NEW: A dedicated wrapper for the link AND the table (changed to fieldset).
//     $form['example_wrapper']['download_example_link_wrapper'] = [
//         '#type' => 'fieldset', // Changed from 'container' to 'fieldset'
//         '#title' => $this->t('Example Details'), // Added a title for the fieldset
//         '#attributes' => ['id' => 'download-example-link-wrapper'],
//         // The states array ensures the whole fieldset is invisible if no example is selected.
//         '#states' => ['invisible' => [':input[name="examples"]' => ['value' => 0]]],
//     ];
    
//     // Example Download Link (now inside the fieldset)
//     $form['example_wrapper']['download_example_link_wrapper']['example_download'] = [
//         '#type' => 'item',
//         '#markup' => Link::fromTextAndUrl(
//             $this->t('Download OpenFOAM code for the example' ),
//             Url::fromRoute('textbook_companion.download_example', ['example_id' => $selected_example])
//         )->toString(),
//         // State is redundant here since it's on the fieldset, but harmless.
//     ];

//     // //  code for download the file and display the filename and type
    
//     // NOTE: It seems you meant to use $selected_example instead of $form_state->getValue('example_file_id') 
//     // to query for the files associated with the currently selected example.
//     // Assuming $selected_example is the correct ID to query.
//     $example_id = $selected_example; // Use the ID of the selected example.

//     $query = \Drupal::database()->select('textbook_companion_example_files', 's');
//     $query->fields('s'); // All fields
//     $query->condition('example_id', $example_id);
//     $results = $query->execute();

//     $example_files_rows = [];

//     if ($results) {
//         while ($row = $results->fetchObject()) {

//             switch ($row->filetype) {
//                 case 'S':
//                     $file_type = $this->t('Source or Main file');
//                     break;
//                 case 'R':
//                     $file_type = $this->t('Result file');
//                     break;
//                 case 'X':
//                     $file_type = $this->t('xcos file');
//                     break;
//                 default:
//                     $file_type = $this->t('Unknown');
//             }

//             $example_files_rows[] = [
//                 Link::fromTextAndUrl(
//                     $row->filename,
//                     Url::fromRoute('textbook_companion.download_file', ['id' => $row->id])
//                 )->toRenderable(),
//                 $file_type,
//             ];
//         }
//     }

//     // Add table inside the dedicated wrapper/fieldset
//     $table = [
//         '#type' => 'table',
//         '#header' => [$this->t('Filename'), $this->t('Type')],
//         '#rows' => $example_files_rows,
//         // '#empty' => $this->t('No individual files found for this example.'), // Added empty text
//         '#attributes' => [
//             'style' => 'width: 100%;',
//         ],
//     ];

//     $form['example_wrapper']['download_example_link_wrapper']['example_files_table'] = $table; // Placed inside the fieldset 
    

//  return $form;
//   }
// ... (code before example selection) ...

    $selected_example = $form_state->getValue('examples') ?? 0;

    // ---------------------------
    // This wrapper contains everything that depends on the selected chapter.
    $form['example_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'example-wrapper'],
    ];

    $form['example_wrapper']['examples'] = [
      '#type' => 'select',
      '#title' => $this->t('Example No. (Caption):'),
      '#options' => $this->_list_of_examples($chapter_id),
      '#ajax' => [
        'callback' => '::ajax_example_changed_callback',
        // Target the fieldset wrapper for update
        'wrapper' => 'download-example-link-wrapper',
      ],
      '#states' => ['invisible' => [':input[name="chapter"]' => ['value' => 0]]],
    ];

    // ✅ Fieldset wrapper for the link AND the table.
    $form['example_wrapper']['download_example_link_wrapper'] = [
        '#type' => 'fieldset', // Now a fieldset
        // '#title' => $this->t('Example Files and Download'), 
        '#attributes' => ['id' => 'download-example-link-wrapper'],
        // Hide the whole fieldset if no example is selected
        // '#states' => ['invisible' => [':input[name="examples"]' => ['value' => 0]]],
    ];
    
    // 1. Download Link (Appears first/above)
    $form['example_wrapper']['download_example_link_wrapper']['example_download'] = [
        '#type' => 'item',
        '#markup' => Link::fromTextAndUrl(
            $this->t('Download OpenFOAM code for the example' ),
            Url::fromRoute('textbook_companion.download_example', ['example_id' => $selected_example])
        )->toString(),
    ];

    // ---------------------------
    // Code to build the file table
    // ---------------------------
    $example_file_id = $selected_example; 

    $query = \Drupal::database()->select('textbook_companion_example_files', 's');
    $query->fields('s'); 
    $query->condition('example_id', $example_id);
    $results = $query->execute();

    $example_files_rows = [];

    if ($results) {
        while ($row = $results->fetchObject()) {

            switch ($row->filetype) {
                case 'S':
                    $file_type = $this->t('Source or Main file');
                    break;
                case 'R':
                    $file_type = $this->t('Result file');
                    break;
                case 'X':
                    $file_type = $this->t('xcos file');
                    break;
                default:
                    $file_type = $this->t('Unknown');
            }

            $example_files_rows[] = [
                Link::fromTextAndUrl(
                    $row->filename,
                    Url::fromRoute('textbook_companion.download_file', ['id' => $example_file_id])
                )->toRenderable(),
                $file_type,
            ];
        }
    }

    // 2. The Table (Appears second/below)
    $table = [
        '#type' => 'table',
        '#header' => [$this->t('Filename'), $this->t('Type')],
        '#rows' => $example_files_rows,
        '#empty' => $this->t('No individual files found for this example.'),
        '#attributes' => [
            'style' => 'width: 100%;',
        ],
    ];

    $form['example_wrapper']['download_example_link_wrapper']['example_files_table'] = $table;
    
    // ... (rest of your buildForm function) ...

 return $form;
  }



  // ---------------------------
  // AJAX CALLBACKS
  // ---------------------------

  
  public function ajax_book_changed_callback(array &$form, FormStateInterface $form_state) {
    // Return the wrapper that contains everything dependent on the book selection.
    return $form['book_dependents_wrapper'];
  }

  public function ajax_chapter_changed_callback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $chapter_id = $form_state->getValue('chapter') ?? 0;
    
    // 1. Update the examples select field and its dependent link wrapper.
    // This is the original part that returns the 'example-wrapper'.
    $response->addCommand(new HtmlCommand('#example-wrapper', $form['example_wrapper']));

    // 2. ✅ FIX: Dynamically build the new chapter download link and update the item's markup.
    $link_markup = $this->getChapterDownloadLinkMarkup($chapter_id);
    
    // We update the specific item inside the wrapper.
    $response->addCommand(new HtmlCommand(
        '#chapter-download-link-wrapper', 
        $form['book_dependents_wrapper']['chapter_download_wrapper']['chapter_download_item']['#markup'] = $link_markup
    ));
    
    
    return $response;
  }
// public function ajax_example_changed_callback(array &$form, FormStateInterface $form_state) {
//     // Return the wrapper containing both the example download link and the files table.
//     return $form['example_details_wrapper'];
//   }
  
  public function ajax_example_changed_callback(array &$form, FormStateInterface $form_state) {
    // ✅ UPDATED: Return the new wrapper containing just the example download link.
    return $form['example_wrapper']['download_example_link_wrapper'];
  }
  //  ---------------------------
  // HELPER FUNCTIONS
  // ---------------------------

  public function _list_of_books($category_default_value = 0) {
    $book_titles = [0 => $this->t('Please select ...')];
    $connection = \Drupal::database();

    $subquery = $connection->select('textbook_companion_proposal', 'tcp');
    $subquery->fields('tcp', ['id']);
    $subquery->condition('proposal_status', 3);

    $query = $connection->select('textbook_companion_preference', 'tcp');
    $query->fields('tcp', ['id', 'book', 'author']);
    $query->condition('category', $category_default_value);
    $query->condition('approval_status', 1);
    $query->condition('proposal_id', $subquery, 'IN');
    $query->orderBy('book', 'ASC');
    $results = $query->execute()->fetchAll();

    foreach ($results as $book) {
      $book_titles[$book->id] = $book->book . ' (Written by ' . $book->author . ')';
    }

    return $book_titles;
  }

  
  public function ajax_example_files_callback($example_id) {
    if (!$example_id) {
        return ['#markup' => ''];
    }

    $connection = \Drupal::database();
    // Assuming the table name is 'textbook_companion_example_file'
    $query = $connection->select('textbook_companion_example_file', 'tcef');
    $query->fields('tcef', ['id', 'filename', 'filetype']);
    $query->condition('example_id', $example_id);
    $query->orderBy('filename', 'ASC');
    $results = $query->execute()->fetchAll();

    if (empty($results)) {
        return ['#markup' => ''];
    }

    $header = [
        'filename' => $this->t('Filename'),
        'filetype' => $this->t('Type'),
    ];

    $rows = [];
    foreach ($results as $file) {
        $example_file_type = $this->t('Unknown');
        switch ($file->filetype) {
            case 'S':
                $example_file_type = $this->t('Source or Main file');
                break;
            case 'R':
                $example_file_type = $this->t('Result file');
                break;
            case 'X':
                $example_file_type = $this->t('xcos file');
                break;
        }

        // Create the linked filename. Adjust the route name if necessary.
        $link = Link::fromTextAndUrl(
            $file->filename,
            Url::fromRoute('textbook_companion.download_file', ['file_id' => $file->id])
        )->toString();
        
        $rows[] = [
            // Ensure rows are simple arrays or use the 'data' structure
            // as necessary for your specific Drupal theme.
            // Using a simple array for row data is often sufficient.
            ['#markup' => $link],
            ['#markup' => $example_file_type],
        ];
    }

    return [
        '#type' => 'table',
        // ✅ REMOVED: Remove the '#caption' element to match the image.
        '#header' => $header,
        '#rows' => $rows,
        // ✅ UPDATED: Add a custom class for styling the table header.
        '#attributes' => ['class' => ['textbook-companion-files', 'textbook-companion-example-file-list']],
        '#empty' => $this->t('No files found for this example.'),
    ];
  }
  public function _html_book_info($preference_id) {
    $connection = \Drupal::database();

    $query = $connection->select('textbook_companion_proposal', 'proposal');
    $query->leftJoin('textbook_companion_preference', 'preference', 'proposal.id = preference.proposal_id');
    $query->addField('preference', 'book', 'preference_book');
    $query->addField('preference', 'author', 'preference_author');
    $query->addField('preference', 'isbn', 'preference_isbn');
    $query->addField('preference', 'publisher', 'preference_publisher');
    $query->addField('preference', 'edition', 'preference_edition');
    $query->addField('preference', 'year', 'preference_year');
    $query->addField('proposal', 'full_name', 'proposal_full_name');
    $query->addField('proposal', 'faculty', 'proposal_faculty');
    $query->addField('proposal', 'reviewer', 'proposal_reviewer');
    $query->addField('proposal', 'course', 'proposal_course');
    $query->addField('proposal', 'branch', 'proposal_branch');
    $query->addField('proposal', 'university', 'proposal_university');
    $query->condition('preference.id', $preference_id);

    $book_details = $query->execute()->fetchObject();
    if (!$book_details) {
      return '';
    }

    $html_data = '<table style="width:100%;" border="0">';
    $html_data .= '<tr><td style="width:50%;vertical-align:top;">';
    $html_data .= '<strong>About the Book</strong><ul>';
    $html_data .= '<li><strong>Author:</strong> ' . $book_details->preference_author . '</li>';
    $html_data .= '<li><strong>Title:</strong> ' . $book_details->preference_book . '</li>';
    $html_data .= '<li><strong>Publisher:</strong> ' . $book_details->preference_publisher . '</li>';
    $html_data .= '<li><strong>Year:</strong> ' . $book_details->preference_year . '</li>';
    $html_data .= '<li><strong>Edition:</strong> ' . $book_details->preference_edition . '</li>';
    $html_data .= '</ul></td><td style="width:50%;vertical-align:top;">';
    $html_data .= '<strong>About the Contributor</strong><ul>';
    $html_data .= '<li><strong>Name:</strong> ' . $book_details->proposal_full_name . '</li>';
    $html_data .= '<li><strong>Faculty:</strong> ' . $book_details->proposal_faculty . '</li>';
    $html_data .= '<li><strong>Reviewer:</strong> ' . $book_details->proposal_reviewer . '</li>';
    $html_data .= '<li><strong>Course:</strong> ' . $book_details->proposal_course . ', ' . $book_details->proposal_branch . ', ' . $book_details->proposal_university . '</li>';
    $html_data .= '</ul></td></tr></table>';

    return $html_data;
  }

  public function _list_of_chapters($preference_id = 0) {
    $book_chapters = [0 => $this->t('Please select...')];
    if (!$preference_id) return $book_chapters;

    $connection = \Drupal::database();
    $query = $connection->select('textbook_companion_chapter', 'tcc');
    $query->fields('tcc', ['id', 'name', 'number']);
    $query->condition('preference_id', $preference_id);
    $query->orderBy('number', 'ASC');
    $results = $query->execute()->fetchAll();

    foreach ($results as $chapter) {
      $book_chapters[$chapter->id] = $chapter->number . '. ' . $chapter->name;
    }

    return $book_chapters;
  }

 public function _list_of_examples($chapter_id = 0) {
    // Return early if no chapter is selected.
    if (!$chapter_id) {
      return [0 => $this->t('Please select...')];
    }

    $connection = \Drupal::database();
    $query = $connection->select('textbook_companion_example', 'tce');
    $query->fields('tce', ['id', 'number', 'caption']);
    $query->condition('chapter_id', $chapter_id);
    $query->condition('approval_status', 1);
    $results = $query->execute()->fetchAll();

    $examples = [];
    foreach ($results as $example) {
      $examples[$example->id] = $example->number . ' (' . $example->caption . ')';
    }

    // ✅ STEP 1: Sort ONLY the examples from the database.
    natcasesort($examples);

    // ✅ STEP 2: Create the final array with "Please select..." at the top,
    // then merge the sorted examples into it.
    $options = [0 => $this->t('Please select...')] + $examples;

    return $options;
  }

  
    public function submitForm(array &$form, FormStateInterface $form_state) {
    }
}