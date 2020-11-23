<?php

/**
 * @return
 * Contains \Drupal\ever\Controller\EverController.
 */
namespace Drupal\ever\Controller;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\file\Entity\File;
use TYPO3\PharStreamWrapper\Interceptor\PharMetaDataInterceptor;

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
    $posts_index = file_get_contents('modules/custom/ever/templates/posts.html.twig');
    return $posts_index;
  }

  public function isAdmin() {
    global $_ever_is_admin;
    if (\Drupal::currentUser()->hasPermission('administer site configuration')) {
      $_ever_is_admin = TRUE;
    }
    return $_ever_is_admin;
  }

  public function renderPosts() {
    $posts['posts'] = [
      '#type' => 'inline_template',
      '#template' => $this->getTemplate(),
      '#context' => [
        'users' => $this->getDbValues(),
        'admin' => $this->isAdmin(),
      ],
    ];
    $posts['form'] = \Drupal::formBuilder()->getForm('Drupal\ever\Form\EverForm');
    return $posts;
  }
  public function postDelete($id) {
    \Drupal::database()->delete('ever')->condition('id', $id)->execute();


    return $this->renderPosts();
  }
}
