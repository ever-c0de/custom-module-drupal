<?php

namespace Drupal\ever\Form;

use Drupal;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Implements the EverForm form controller.
 *
 * This form designed for submit and collect the user feedback.
 * It's extends the FormBase controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class EverForm extends FormBase {
  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'ever_form';
  }

  /**
   * Build our form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   * @param null $id
   *   Argument that we take from EverController::postUpdate($id).
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    Drupal::messenger()->deleteByType('error');

    $form['title'] = [
      '#type' => 'inline_template',
      '#template' => "<h2 class=\"ever_module__title\">Give your feedback about us!</h2>",
      '#prefix' => '<div class="form-ever-submit">',
    ];
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name'),
      '#description' => $this->t("Your name can't be longer than 100 characters and have any numbers."),
      '#default_value' => '',
      '#maxlength' => 100,
      '#required' => TRUE,
      '#prefix' => '<div class="form-ever-inner">
                        <div class="information-fields">',
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => 'Email address',
      '#description' => t("Email needs to start with letter or number."),
      '#default_value' => '',
      '#required' => TRUE,
      '#maxlength' => 100,
    ];

    $form['phone_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Your phone number'),
      '#description' => t("Phone number must accord this format: +38(XXX)XXX-XX-XX."),
      '#default_value' => '',
      '#required' => TRUE,
      '#maxlength' => 17,
    ];

    $form['comment'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your comment'),
      '#description' => t("Your comment needs to be not longer than 500 characters."),
      '#default_value' => '',
      '#resizable' => FALSE,
      '#required' => TRUE,
      '#maxlength' => 500,
      '#cols' => 10,
      '#rows' => 4,
      '#suffix' => '</div>
<div class="upload-fields">',
    ];

    $form['avatar_photo'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Your avatar photo'),
      '#description' => t('Allowed extensions: png jpg jpeg'),
      '#default_value' => NULL,
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
      '#description' => t('Allowed extensions: png jpg jpeg'),
      '#default_value' => NULL,
      '#upload_location' => 'public://images/photos/',
      '#required' => FALSE,
      '#multiple' => FALSE,
      '#upload_validators' => [
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

    $id = $form_state->getBuildInfo();
    if ($id['args'] != FALSE) {
      $form['title'] = [
        '#type' => 'inline_template',
        '#template' => "<h2 class=\"ever_module__title\">Update this post!</h2>",
        '#prefix' => '<div class="form-ever-submit">',
      ];
      $form['actions']['submit'] = [];
      $form['actions']['update'] = [
        '#type' => 'submit',
        '#value' => $this->t('Update'),
        '#button_type' => 'primary',
        '#ajax' => [
          'callback' => '::ajaxSubmitCallback',
          'event' => 'click',
          'progress' => [
            'type' => 'throbber',
          ],
        ],
      ];
    }
    return $form;
  }

  /**
   * Callback for fields validation and messages render.
   *
   * Validate fields and return error messages or continue to submit.
   *
   * @param array $form
   *   Gets an array form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Gets the form_state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Show errors or continue to submit.
   */
  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {
    $errors = $form_state->getErrors();
    $ajax_response = new AjaxResponse();

    if (count($errors) === 0) {
      Drupal::messenger()->deleteByType('error');
      $ajax_response->addCommand(new RedirectCommand('http://ever.loc/module-page'));
    }
    else {
      $message = [
        '#theme' => 'status_messages',
        '#message_list' => Drupal::messenger()->all(),
        '#status_headings' => [
          'status' => t('Status message'),
          'error' => t('Error message'),
          'warning' => t('Warning message'),
        ],
      ];
      $messages = Drupal::service('renderer')->render($message);
      $ajax_response->addCommand(new HtmlCommand('#form-system-messages', $messages));
    }
    return $ajax_response;
  }

  /**
   * Validate fields with user data.
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    Drupal::messenger()->deleteByType('error');
    if (strlen($form_state->getValue('name')) < 2) {
      $form_state->setErrorByName('name', $this->t('Name need to be longer.'));
    }
    if (preg_match("/^[\w'\-,.][^0-9_!¡?÷?¿/\\+=@#$%ˆ&*(){}|~<>;:[\]]{2,}$/", $form_state->getValue('name')) === 0) {
      $form_state->setErrorByName('name', $this->t('Your name @name is not valid.',
        ['@name' => $form_state->getValue('name')]));
    }
    else {
      Drupal::messenger()->deleteByType('error');
    }
    if (preg_match("/^(?!.*@.*@.*$)(?!.*@.*\-\-.*\..*$)(?!.*@.*\-\..*$)(?!.*@.*\-$)(.*@.+(\..{1,11})?)$/", $form_state->getValue('email')) === 0) {
      $form_state->setErrorByName('email', $this->t('The email address @email is not valid.',
        ['@email' => $form_state->getValue('email')]));
    }
    else {
      Drupal::messenger()->deleteByType('error');
    }
    if (preg_match('/(^(?!\+.*\(.*\).*\-\-.*$)(?!\+.*\(.*\).*\-$)(\+[0-9]{1,3}\([0-9]{1,3}\)[0-9]{1}([-0-9]{0,8})?([0-9]{0,1})?)$)|(^[0-9]{1,4}$)/', $form_state->getValue('phone_number')) === 0) {
      $form_state->setErrorByName('phone_number', $this->t('The telephone number @tel is not valid.',
        ['@tel' => $form_state->getValue('phone_number')]));
    }
    else {
      Drupal::messenger()->deleteByType('error');
    }
  }

  /**
   * Form submit.
   *
   * {@inheritdoc}
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $id = $form_state->getBuildInfo();
    if ($id['args'] != TRUE) {
      $avatar = $form_state->getValue('avatar_photo');
      if (count($avatar) !== 0) {
        $file = File::load($avatar[0]);
        $file->setPermanent();
        $file->save();
        $avatar_id = $avatar[0];
      }

      $photo = $form_state->getValue('comment_photo');
      if (count($photo) !== 0) {
        $file = File::load($photo[0]);
        $file->setPermanent();
        $file->save();
        $photo_id = $photo[0];
      }

      Drupal::database()->insert('ever')->fields([
        'name' => $form_state->getValue('name'),
        'email' => $form_state->getValue('email'),
        'tel' => $form_state->getValue('phone_number'),
        'comment' => $form_state->getValue('comment'),
        'avatarDir' => $avatar_id,
        'photoDir' => $photo_id,
        'timestamp' => date("m-d-y | H:i:s", time()),
      ])->execute();

      Drupal::messenger()->addMessage($this->t('Thank you for feedback, @name.',
        ['@name' => $form_state->getValue('name')]));
    }
    if ($id['args'] != FALSE) {
      $avatar = $form_state->getValue('avatar_photo');
      if (count($avatar) !== 0) {
        $file = File::load($avatar[0]);
        $file->setPermanent();
        $file->save();
        $avatar_id = $avatar[0];
      }

      $photo = $form_state->getValue('comment_photo');
      if (count($photo) !== 0) {
        $file = File::load($photo[0]);
        $file->setPermanent();
        $file->save();
        $photo_id = $photo[0];
      }

      Drupal::database()
        ->update('ever')
        ->condition('id', $id['args'])
        ->fields([
          'name' => $form_state->getValue('name'),
          'email' => $form_state->getValue('email'),
          'tel' => $form_state->getValue('phone_number'),
          'comment' => $form_state->getValue('comment'),
          'avatarDir' => $avatar_id,
          'photoDir' => $photo_id,
          'timestamp' => date("m-d-y | H:i:s", time()),
        ])->execute();

      Drupal::messenger()->addMessage($this->t('Post was updated!'));
    }
  }

}
