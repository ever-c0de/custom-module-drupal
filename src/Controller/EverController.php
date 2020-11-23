<?php
/**
 * @return
 * Contains \Drupal\ever\Controller\EverController.
 */
namespace Drupal\ever\Controller;


use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EverController {

  public function getDbValues() {

  }

  public function postDelete($id) {
    $postDelete = \Drupal::database()->delete('ever')->condition('id', $id)->execute();
    return \Drupal::formBuilder()->getForm('Drupal\ever\Form\EverForm');
  }

}
