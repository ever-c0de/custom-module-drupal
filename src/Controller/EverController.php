<?php
/**
 * @return
 * Contains \Drupal\ever\Controller\EverController.
 */
namespace Drupal\ever\Controller;


class EverController
{
  public function content()
  {
    $element = array(
      '#markup' => 'Hello World!',
    );
    return $element;
  }
}
