<?php

namespace Drupal\textbook_companion\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Database\Database;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Markup;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RendererInterface;

class TextbookCompanionRunForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'textbook_companion_run_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

	    // $options_first =$this->_list_of_books($book_default_value);
    // $options_two = $this->_ajax_get_experiment_list();
    // Get the book preference ID from route parameter
$url_book_pref_id = \Drupal::request()->attributes->get('book_pref_id') ?? 0;

	// var_dump($url_book_pref_id);die;
    $category_default_value = 0;

    if ($url_book_pref_id) {
      $query = \Drupal::database()->select('textbook_companion_preference', 't');
      $query->fields('t', ['category']);
      $query->condition('id', $url_book_pref_id);
      $result = $query->execute()->fetchObject();
      $category_default_value = $result ? $result->category : 0;
    }

    // Hidden category field
    // $form['category'] = [
    //   '#type' => 'hidden',
    //   '#title' => $this->t('Category'),
    //   '#default_value' => $category_default_value,
    //   '#ajax' => [
    //     'callback' => [$this, 'ajaxBookListCallback'],
    //   ],
    // ];

    // Book select field
$$category_default_value = $result ? $result->category : 0; // fetched from textbook_companion_preference
 $book_default_value = $url_book_pref_id ?: 0;
    // Book details
    $book_info = $book_default_value ? $this->_html_book_info($book_default_value) : '';

$form['book'] = [
  '#type' => 'select',
  '#title' => $this->t('Title of the book'),
  '#options' => $this->_list_of_books($category_default_value),
  '#default_value' => $url_book_pref_id,
  '#ajax' => [
    'callback' => '::ajax_book_list_callback',
    'wrapper' => 'ajax-book-details', // ✅ FIXED
  ],
];

$form['book_details_wrapper'] = [
  '#type' => 'container',
  '#attributes' => ['id' => 'ajax-book-details'], // ✅ MUST MATCH
];

$form['book_details_wrapper']['book_details'] = [
  '#type' => 'item',
  '#markup' => $book_info,
];

    // $form['download_book_wrapper']['book_details'] = [
    //   '#type' => 'item',
    //   '#markup' => '<div id="ajax-book-details-replace">' . $book_info . '</div>',
    // ];
	 $form['download_book'] = [
  '#type' => 'item',
  '#markup' => Link::fromTextAndUrl(
    $this->t('Download'),
    Url::fromRoute('textbook_companion.download_book', ['book_id' => $book_default_value])
  )->toString() . ' ' . $this->t('(Download the OpenFOAM codes for all the solved examples)'),
];

// -----------------------------------------Chapter------------------------------------------------------------
    // Chapter select
    // $form['chapter'] = [
    //   '#type' => 'select',
    //   '#title' => $this->t('Title of the chapter'),
    //   '#options' => $this->_list_of_chapters($book_default_value),
    //   '#prefix' => '<div id="ajax-chapter-list-replace">',
    //   '#suffix' => '</div>',
    //   '#ajax' => [
    //     'callback' => '::ajax_chapter_list_callback',
    //     'wrapper' => '::ajax_selected_chapter'
    //   ],
    //   '#states' => [
    //     'invisible' => [
    //       ':input[name="book"]' => ['value' => 0],
    //     ],
    //   ],
    // ];

  $form['chapter_wrapper'] = [
  '#type' => 'container',
  '#attributes' => ['id' => 'ajax-chapter-wrapper'],
];

$form['chapter_wrapper']['chapter'] = [
  '#type' => 'select',
  '#title' => $this->t('Title of the chapter'),
  '#options' => $this->_list_of_chapters($book_default_value),
  '#ajax' => [
    'callback' => '::ajax_chapter_list_callback',
    'wrapper' => 'ajax_selected_chapter',
  ],
  '#states' => [
    'invisible' => [
      ':input[name="book"]' => ['value' => 0],
    ],
  ],
];    
$form['download_chapter_wrapper'] = [
      '#type' => 'item',
      '#markup' => '<div id="ajax-book-details-replace">'  . '</div>',
    ];
	 $form['download_chapter_wrapper']['download_chapter'] = [
  '#type' => 'item',
  '#markup' => Link::fromTextAndUrl(
    $this->t('Download Chapter'),
    Url::fromRoute('textbook_companion.download_chapter', ['chapter_id' => $book_default_value])
  )->toString() . ' ' . $this->t('(Download the OpenFOAM codes for all the solved examples)'),
];
// ----------------Exapmle-------------------------------
    $example_default_value = $form_state->getValue('chapter') ?? '';
    // $chapter_id = $form_state->getValue('chapter') ?? $chapter_default_value ?? 0;
$form['examples']['#options'] = $this->_list_of_examples($chapter_id);


  $form['example_wrapper'] = [
  '#type' => 'container',
  '#attributes' => ['id' => 'ajax-example-wrapper'],
];
    $form['examples'] = [
        '#type' => 'select',
        '#title' => $this->t('Example No. (Caption):'),
        '#options' => $this->_list_of_examples($example_default_value),
        '#default_value' => $form_state->getValue('examples') ?? '',
        '#ajax' => [
            'callback' => '::ajax_example_list_callback',
            'wrapper' => 'ajax-selected_list',
        ],
        '#states' => [
            'invisible' => [
                ':input[name="book"]' => ['value' => 0],
            ],
        ],
    ];

    $form['download_example_wrapper'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax-download-example-code-replace"></div>',
    ];
$form['download_example_wrapper']['download_example'] = [
  '#type' => 'item',
  '#markup' => Link::fromTextAndUrl(
    $this->t('Download Example'),
    Url::fromRoute('textbook_companion.download_example', ['chapter_id' => $chapter_id])
) ->toString() . $this->t('(Download the OpenFOAM codes for all the solved examples)'),
// Url::fromRoute('textbook_companion.download_example', ['chapter_id' => $chapter_id])

];
    // $form['example_files'] = [
    //     '#type' => 'item',
    //     '#markup' => '<div id="ajax-download-example-files-replace"></div>',
    // ];



    // Examples select
    // $example_default_value = $form_state->getValue('chapter') ?: '';
    // $form['examples'] = [
    //   '#type' => 'select',
    //   '#title' => $this->t('Example No. (Caption)'),
    //   '#options' => $this->_list_of_examples($example_default_value),
    //   '#default_value' => $form_state->getValue('examples') ?? '',
    //   '#prefix' => '<div id="ajax-example-list-replace">',
    //   '#suffix' => '</div>',
    //   '#ajax' => [
    //     'callback' => [$this, 'ajaxExampleFilesCallback'],
    //   ],
    //   '#states' => [
    //     'invisible' => [
    //       ':input[name="book"]' => ['value' => 0],
    //     ],
    //   ],
    // ];

    // Placeholder items for AJAX content
    $form['download_chapter'] = ['#type' => 'item', '#markup' => '<div id="ajax-download-chapter-replace"></div>'];
    $form['download_example_code'] = ['#type' => 'item', '#markup' => '<div id="ajax-download-example-code-replace"></div>'];
    $form['example_files'] = ['#type' => 'item', '#markup' => '<div id="ajax-download-example-files-replace"></div>'];

    return $form;
  }

/**
 * Get list of books for a category.
 */
// public function ajax_book_list_callback(array &$form, FormStateInterface $form_state) {
//   return $form['download_book_wrapper'];
// }
public function ajax_book_list_callback(array &$form, FormStateInterface $form_state) {
    $selected_book = $form_state->getValue('book');
    $book_info = $selected_book ? $this->_html_book_info($selected_book) : '';

    // Update only the book details part
    $form['book_details_wrapper']['book_details']['#markup'] = $book_info;
    return $form['book_details_wrapper'];
}

public function ajax_book_details_callback(array &$form, FormStateInterface $form_state) {
  // Get the selected book from form state
  $selected_book = $form_state->getValue('book');
  
  // Update markup dynamically
  $form['book_details_wrapper']['#markup'] = $selected_book ? _html_book_info($selected_book) : '';
  
  return $form['book_details_wrapper'];
}
public function ajax_example_list_callback(array &$form, FormStateInterface $form_state) {
  return $form['chapter_wrapper'];
}

public function ajaxExampleFilesCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $selected_example = $form_state->getValue('examples');

    // Generate file list or download button based on $selected_example
    $html = '<p>Selected Example ID: ' . $selected_example . '</p>';

    // Update the example files section
    $response->addCommand(new HtmlCommand('#ajax-download-example-files-replace', $html));

    return $response;
}

function _list_of_books($category_default_value = 0) {
  $book_titles = [
    0 => t('Please select ...'),
  ];

  $connection = \Drupal::database();

  // Subquery to get proposal IDs with status 3
  $subquery = $connection->select('textbook_companion_proposal', 'tcp');
  $subquery->fields('tcp', ['id']);
  $subquery->condition('proposal_status', 3);

  // Main query
  $query = $connection->select('textbook_companion_preference', 'tcp');
  $query->fields('tcp', ['id', 'book', 'author']);
  $query->condition('category', $category_default_value); // use selected category
  $query->condition('approval_status', 1);
  $query->condition('proposal_id', $subquery, 'IN');
  $query->orderBy('book', 'ASC');

  $results = $query->execute()->fetchAll();

  foreach ($results as $book) {
    $book_titles[$book->id] = $book->book . ' (Written by ' . $book->author . ')';
  }

  return $book_titles;
}

function _html_book_info($preference_id) {
  $connection = \Drupal::database();

  // Main query with LEFT JOIN
  $query = $connection->select('textbook_companion_proposal', 'proposal');
  $query->leftJoin('textbook_companion_preference', 'preference', 'proposal.id = preference.proposal_id');

  // Select fields
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

  // Condition
  $query->condition('preference.id', $preference_id);

  // Execute
  $book_details = $query->execute()->fetchObject();

  if (!$book_details) {
    return '';
  }

  // Build HTML
  $html_data = '<table cellspacing="1" cellpadding="1" border="0" style="width: 100%;" valign="top">';
  $html_data .= '<tr>';
  $html_data .= '<td style="width: 35%;"><span style="color: rgb(128, 0, 0);"><strong>About the Book</strong></span></td>';
  $html_data .= '<td style="width: 35%;"><span style="color: rgb(128, 0, 0);"><strong>About the Contributor</strong></span></td>';
  $html_data .= '</tr>';
  $html_data .= '<tr>';
  $html_data .= '<td valign="top"><ul>';
  $html_data .= '<li><strong>Author:</strong> ' . $book_details->preference_author . '</li>';
  $html_data .= '<li><strong>Title of the Book:</strong> ' . $book_details->preference_book . '</li>';
  $html_data .= '<li><strong>Publisher:</strong> ' . $book_details->preference_publisher . '</li>';
  $html_data .= '<li><strong>Year:</strong> ' . $book_details->preference_year . '</li>';
  $html_data .= '<li><strong>Edition:</strong> ' . $book_details->preference_edition . '</li>';
  $html_data .= '</ul></td>';

  $html_data .= '<td valign="top"><ul>';
  $html_data .= '<li><strong>Contributor Name:</strong> ' 
    . $book_details->proposal_full_name . ', ' 
    . $book_details->proposal_course . ', ' 
    . $book_details->proposal_branch . ', ' 
    . $book_details->proposal_university . '</li>';
  $html_data .= '<li><strong>College Teacher:</strong> ' . $book_details->proposal_faculty . '</li>';
  $html_data .= '<li><strong>Reviewer:</strong> ' . $book_details->proposal_reviewer . '</li>';
  $html_data .= '</ul></td>';

  $html_data .= '</tr></table>';

  return $html_data;
}

function _list_of_chapters($preference_id = 0) {
  $book_chapters = [
    0 => t('Please select...'),
  ];

  if (!$preference_id) {
    return $book_chapters;
  }

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
function _list_of_examples($chapter_id = 0) {
  $book_examples = [
    0 => t('Please select...'),
  ];

  if (!$chapter_id) {
    return $book_examples;
  }

  $connection = \Drupal::database();

  $query = $connection->select('textbook_companion_example', 'tce');
  $query->fields('tce', ['id', 'number', 'caption']);
  $query->condition('chapter_id', $chapter_id);
  $query->condition('approval_status', 1);

  // You can add custom ordering if needed. Example:
  // $query->orderBy('number', 'ASC'); 

  $results = $query->execute()->fetchAll();

  foreach ($results as $example) {
    $book_examples[$example->id] = $example->number . ' (' . $example->caption . ')';
  }

  return $book_examples;
}function _book_information($preference_id)
  {
    /*$book_data = db_fetch_object(db_query("SELECT
    preference.book as preference_book, preference.author as preference_author, preference.isbn as preference_isbn, preference.publisher as preference_publisher, preference.edition as preference_edition, preference.year as preference_year,
    proposal.full_name as proposal_full_name, proposal.faculty as proposal_faculty, proposal.reviewer as proposal_reviewer, proposal.course as proposal_course, proposal.branch as proposal_branch, proposal.university as proposal_university
    FROM {textbook_companion_proposal} proposal LEFT JOIN {textbook_companion_preference} preference ON proposal.id = preference.proposal_id WHERE preference.id = %d", $preference_id));*/
    $query = db_select('textbook_companion_proposal', 'proposal');
    $query->fields('preference', array(
        'book',
        'author',
        'isbn',
        'publisher',
        'edition',
        'year'
    ));
    $query->fields('proposal', array(
        'full_name',
        'faculty',
        'reviewer',
        'course',
        'branch',
        'university'
    ));
    $query->leftJoin('textbook_companion_preference', 'preference', 'proposal.id = preference.proposal_id');
    $query->condition('preference.id', $preference_id);
    $book_data = $query->execute()->fetchObject();
    return $book_data;
  }

  /**
   * {@inheritdoc}
   */
  

  /**
   * AJAX callbacks placeholders
   */
  public function ajaxBookListCallback(array &$form, FormStateInterface $form_state) {
    return new AjaxResponse();
  }

  public function ajaxChapterListCallback(array &$form, FormStateInterface $form_state) {
    return new AjaxResponse();
  }

  public function ajaxExampleListCallback(array &$form, FormStateInterface $form_state) {
    return new AjaxResponse();
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty submit handler
  }
}
