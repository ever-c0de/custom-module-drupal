<?php

/**
 * @return
 * Contains \Drupal\ever\Controller\EverController.
 */
namespace Drupal\ever\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use TYPO3\PharStreamWrapper\Interceptor\PharMetaDataInterceptor;


class EverController extends ControllerBase {

  public function getDbValues() {
    $db = \Drupal::database()
      ->select('ever')
      ->fields('ever', [
        'id',
        'name',
        'email',
        'tel',
        'comment',
        'avatarDir',
        'photoDir',
        'timestamp',
      ])
      ->orderBy('id', 'DESC')
      ->execute()
      ->fetchAll();
    foreach ($db as $value) {
      if ($value->avatarDir != NULL) {
        $value->avatarDir = File::load($value->avatarDir)->Url();
      }
      else {
        $value->avatarDir = 'http://ever.loc/modules/custom/ever/default_ever/default_logo.jpg';
      }
      if ($value->photoDir != NULL) {
        $value->photoDir = File::load($value->photoDir)->Url();
      }

    }
    return $db;
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
    return $this->redirect('ever.form');
  }
  // @todo Finish postUpdate() method.
  public function postUpdate($id) {
    if ($this->isAdmin() === TRUE) {
      $post = \Drupal::database()->select('ever')
        ->condition('id', $id)
        ->fields('ever')
        ->execute()
        ->fetchAll();
      $posts = \Drupal::formBuilder()->getForm('Drupal\ever\Form\EverForm');
      $posts['name']['#default_value'] = $post[0]->name;
      $posts['email']['#default_value'] = $post[0]->email;
      $posts['tel']['#default_value'] = $post[0]->tel;
      $posts['comment']['#default_value'] = $post[0]->comment;
      $posts['avatarDir']['#default_value'] = $post[0]->avatarDir;
      $posts['photoDir']['#default_value'] = $post[0]->photoDir;

    }
    return $posts;
  }

}
