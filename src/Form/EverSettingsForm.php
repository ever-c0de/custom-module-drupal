<?php


namespace Drupal\ever\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class EverSettingsForm extends ConfigFormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ever_form_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ever.ever_form.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ever.ever_form.settings');
    $form['default_phone_number'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default phone number'),
      '#default_value' => $config->get('phone_number'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('ever.ever_form.settings')
      ->set('phone_number', $values['default_phone_number'])
      ->save();
  }
}
