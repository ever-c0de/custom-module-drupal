<?php

/**
 * @return
 * Contains \Drupal\ever\Controller\EverController.
 */
namespace Drupal\ever\Controller;

class EverController {

  public function getDbValues() {
    $db = \Drupal::database()->select('ever')->fields('ever', [
      'id',
      'name',
      'email',
      'tel',
      'comment',
      'avatarDir',
      'photoDir',
      'timestamp',
    ]);
    $db_values = $db->execute()->fetchAll();
    $db_values = array_reverse($db_values);
    return $db_values;
  }

  public function getTemplate() {

  }

  public function postDelete($id) {
    $postDelete = \Drupal::database()->delete('ever')->condition('id', $id)->execute();
    return \Drupal::formBuilder()->getForm('Drupal\ever\Form\EverForm');
  }

}
