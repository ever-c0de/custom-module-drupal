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
      '#description'  => t("Your name can't be longer than 100 characters."),
      '#maxlength' => 100,
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => 'Email address',
      '#description'  => t("Email needs to start with letter or number."),
      '#required' => TRUE,
      '#maxlength' => 100,
    ];

    $form['phone_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Your phone number'),
      '#description'  => t("Phone number must accord this format: +38(XXX)XXX-XX-XX."),
      '#required' => TRUE,
      '#maxlength' => 13,
    ];


    $form['comment'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your comment'),
      '#description'  => t("Your comment needs to be not longer than 500 characters."),
      '#resizable' => FALSE,
      '#maxlength' => 500,
      '#cols' => 10,
      '#rows' => 4,
    ];

    $form['avatar_photo'] = [
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
      '#default_value' => NULL,
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
   * @throws \Exception
   */

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $avatar = $form_state->getValue('avatar_photo');
    $file = File::load($avatar[0]);
    $file->setPermanent();
    $file->save();
    $avatar_uri = $file->getFileUri();

    $photo = $form_state->getValue('comment_photo');
    $file = File::load($photo[0]);
    $file->setPermanent();
    $file->save();
    $photo_uri = $file->getFileUri();

    \Drupal::database()->insert('ever')->fields([
      'name' => $form_state->getValue('name'),
      'email' => $form_state->getValue('email'),
      'tel' => $form_state->getValue('phone_number'),
      'comment' => $form_state->getValue('comment'),
      'avatarDir' => $avatar_uri,
      'photoDir' => $photo_uri,
      'timestamp' => date("m-d-y | H:m:s"),
    ])->execute();

    \Drupal::messenger()->addMessage($this->t('Thank you for feedback, @name',
      ['@name' => $form_state->getValue('name')]));
  }

}
