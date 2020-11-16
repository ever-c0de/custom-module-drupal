<?php

namespace Drupal\ever\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\file\Entity\File;

/**
 * @file
 * Contains \Drupal\ever\Form\EverForm.
 *
 */

class EverForm extends FormBase {

  /**
   * {@inheritDoc}.
   *
   */

  public function getFormId() {
    return 'ever_form';
  }

  /**
   *
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    \Drupal::messenger()->deleteByType('error');
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name'),
      '#maxlength' => 100,
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => 'Email address',
      '#required' => TRUE,
      '#maxlength' => 100,
    ];

    $form['phone_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Your phone number'),
      '#required' => TRUE,
      '#maxlength' => 13,
    ];


    $form['comment'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your comment'),
      '#resizable' => FALSE,
      '#maxlength' => 500,
      '#cols' => 10,
      '#rows' => 4,
    ];

    $form['avatar_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Your avatar photo'),
      '#description'  => t('Allowed extensions: png jpg jpeg'),
      '#upload_location' => 'public://images/avatar/',
      '#required' => FALSE,
      '#multiple' => FALSE,
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2097152],
      ],
    ];

    $form['comment_photo'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Your comment photo'),
      '#description'  => t('Allowed extensions: png jpg jpeg'),
      '#upload_location' => 'public://images/photos/',
      '#required' => FALSE,
      '#multiple' => FALSE,
      '#upload_validators'  => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [5242880],
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#button_type' => 'primary',
      '#ajax' => [
        'callback' => '::ajaxSubmitCallback',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
        ],
      ],
    ];

    $form['system_messages'] = [
      '#markup' => '<div id="form-system-messages"></div>',
      '#weight' => -100,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {
    $errors = $form_state->getErrors();
    $ajax_response = new AjaxResponse();

    if (count($errors) === 0) {
      \Drupal::messenger()->deleteByType('error');
      $ajax_response->addCommand(new RedirectCommand('http://ever.loc/module-page'));
    }
    else {
      $message = [
        '#theme' => 'status_messages',
        '#message_list' => \Drupal::messenger()->all(),
        '#status_headings' => [
          'status' => t('Status message'),
          'error' => t('Error message'),
          'warning' => t('Warning message'),
        ],
      ];
      $messages = \Drupal::service('renderer')->render($message);
      $ajax_response->addCommand(new HtmlCommand('#form-system-messages', $messages));
    }
    return $ajax_response;
  }

  /**
   * Валидация отправленых данных в форме.
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()->deleteByType('error');
    if (strlen($form_state->getValue('name')) < 2) {
      $form_state->setErrorByName('name', 'Name need to be longer');
    }
  }

  /**
   * Отправка формы.
   *
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()->addMessage($this->t('Thank you for feedback, @name',
      ['@name' => $form_state->getValue('name')]));

    $entry = [
      'name' => $form_state->getValue('name'),
      'surname' => $form_state->getValue('surname'),
      'age' => $form_state->getValue('age'),
      'uid' => $account->id(),
    ];
/*  $photo = $form_state->getValue('avatar_file');
    $file = File::load($photo[0]);
    $file->setPermanent();
    $file->save();

    $photo = $form_state->getValue('comment_photo');
    $file = File::load($photo[0]);
    $file->setPermanent();
    $file->save();*/
  }

}
