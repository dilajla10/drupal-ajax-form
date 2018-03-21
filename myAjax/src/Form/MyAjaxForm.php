<?php

/**
 * @file
 * Contains Drupal\myAjax\MyAjaxForm
 */

namespace Drupal\myAjax\Form;

use \Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;


class MyAjaxForm extends FormBase {

    public function getFormId() {
        return 'myAjax_form';
      }

 
  
  public function buildForm(array $form, FormStateInterface $form_state) {
   


    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => $this->t('Enter your Title.'),
      '#size' => 60,
      '#maxlength' => 128,
      '#ajax' => [
          'callback' => array($this, 'validateTitleAjax'),
          'event' => 'change',
          'progress' => array(
            'type' => 'throbber',
            'message' => t('Verifying title...'),
          ),
        ],
      '#suffix' => '<span class="title-valid-message"></span>'
    ];



    /*$form['body_text'] = array(
        '#type' => 'text_format',
        '#title' => 'Text',
        '#description' => 'Please enter body text', 
        '#size' => 60,
              '#maxlength' => 128,
              '#ajax' => [
                  'callback' => array($this, 'validateTagNameAjax'),
                  'event' => 'change',
                  'progress' => array(
                    'type' => 'throbber',
                    'message' => t('Verifying tag...'),
                  ),
                ],
        '#suffix' => '<span class="tags-valid-message"></span>'
    );  */

    $form['body_text'] = [
      '#type' => 'text_format',
      '#title' => t('Text body'),
      '#default_value' => $term->description,
      '#format' => $term->format,
      '#weight' => 0,
    ];
  


    $form['field_image']=array(
        '#type' => 'managed_file',
        '#title' => $this->t('Image'),
        '#upload_validators' => array(
            'file_validate_extensions' => array('gif png jpg jpeg'),
             'file_validate_size' => array(25600000),
         ),
        '#upload_location' => 'public://page/',
        '#default_value' => '',
        '#description'   => t('Specify an image(s) to display.'),
        '#states'        => array(
        'visible'      => array(
            ':input[name="image_type"]' => array('value' => t('Upload New Image(s)')),
        ),
    ),
        '#required' => TRUE,
    );
     
  
    $form['submit']=array(
        '#type' => 'submit',
        '#value' => $this->t('save'),
        '#ajax' => [
            'wrapper' => 'my-form-wrapper-id',
        ],
    );
    $form['#prefix'] = '<div id="my-form-wrapper-id">';
    $form['#suffix'] = '</div>';

    return $form;
  }


  protected function validateTitle(array &$form, FormStateInterface $form_state) {
    if (is_numeric($form_state->getValue('title'))){
        return FALSE;
    }
        return TRUE;
}

/*protected function validateTagName(array &$form, FormStateInterface $form_state) {
    if (is_numeric($form_state->getValue('tags'))){
        return FALSE;
    }
        return TRUE;
}*/


  public function validateTitleAjax(array &$form, FormStateInterface $form_state) {
    $valid = $this->validateTitle($form, $form_state);
    $response = new AjaxResponse();
    if ($valid) {
        $css = ['border' => '1px solid green'];
        $message = $this->t('Title is ok.');
    }
    else {
        $css = ['border' => '1px solid red'];
        $message = $this->t('Title is not valid.');
    }
    $response->addCommand(new CssCommand('#edit-title', $css));
    $response->addCommand(new HtmlCommuse('.title-valid-message', $message));
    return $response;
}


/*public function validateTagNameAjax(array &$form, FormStateInterface $form_state) {
    $valid = $this->validateTagName($form, $form_state);
    $response = new AjaxResponse();
    if ($valid) {
        $css = ['border' => '1px solid green'];
        $message = $this->t('Title is ok.');
    }
    else {
        $css = ['border' => '1px solid red'];
        $message = $this->t('Title is not valid.');
    }
    $response->addCommand(new CssCommand('#edit-tags', $css));
    $response->addCommand(new HtmlCommuse('.tags-valid-message', $message));
    return $response;
}*/



public function submitForm(array &$form, FormStateInterface $form_state){
    
drupal_set_message($this->t('You add an article!<br><br><br>'));
    
    $image = $form_state->getValue('field_image');

    /* Load the object of the file by it's fid */ 
       $file = File::load( $image[0] );
    
    /* Set the status flag permanent of the file object */
       $file->setPermanent();
    
    /* Save the file in database */
       $file->save();

/* Fetch the array of the file stored temporarily in database */ 


   /*$file = File::create([
    'uid' => 1,
    'uri' => 'public://images/',
    'status' => 1,
  ]);
  $file->save(); */


  /*$tag_terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadTree('tags');
  $tags = array();
  foreach ($tag_terms as $tag_term) {
      $tags[$tag_term->tid] = $tag_term->name;
  }*/
  
   $node = Node::create([
        'type'        => 'article',
        'title'       => $form_state->getValue('title'),
        'uid' => 1,
        'field_tags' =>[2],
        'body' => $form_state->getValue('body_text'),    
        'field_image' => 
            [
              'target_id' => $file->id(),
              'alt' => "nature 'alt'",
              'title' => "Test 'title'",
            ],
                                  
                                           ]);
                                                  
                                                  $node->save();
                                                  \Drupal::service('path.alias_storage')->save('/node/' . $node->id(), '/form-with-image', 'en');


    }
}





