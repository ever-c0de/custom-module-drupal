<?php

namespace Drupal\ever\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;

/**
 * Class EverController.
 *
 * Implement methods for deletePost() and updatePost().
 *
 * @package Drupal\ever\Controller
 */
class EverController extends ControllerBase {

  /**
   * Designed for getting the full styled posts.
   *
   * @return array
   *   Return the posts.
   */
  public function renderPosts() {
    $posts['posts'] = [
      '#type' => 'inline_template',
      '#template' => $this->getTemplate(),
      '#context' => [
        'users' => $this->getDbValues(),
        'admin' => $this->isAdmin(),
      ],
    ];
    $posts['form'] = Drupal::formBuilder()->getForm('Drupal\ever\Form\EverForm');
    return $posts;
  }

  /**
   * Method gets the template file content and return it.
   *
   * @return false|string
   *   If TRUE -> return template file content.
   */
  public function getTemplate() {
    $posts_index = file_get_contents('modules/custom/ever/templates/posts.html.twig');
    return $posts_index;
  }

  /**
   * Select values from ever database.
   *
   * @return mixed
   *   Return the DB values.
   */
  public function getDbValues() {
    $db = Drupal::database()
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
        $value->avatarDir = 'modules/custom/ever/default_ever/default_logo.jpg';
      }
      if ($value->photoDir != NULL) {
        $value->photoDir = File::load($value->photoDir)->Url();
      }

    }
    return $db;
  }

  /**
   * Statement that check user permission to content.
   *
   * @return bool
   *   Return TRUE if user is admin.
   */
  public function isAdmin() {
    global $_ever_is_admin;
    if (Drupal::currentUser()->hasPermission('administer site configuration')) {
      $_ever_is_admin = TRUE;
    }
    return $_ever_is_admin;
  }

  /**
   * Method for deleting the post.
   *
   * @param int $id
   *   Take parameter from route file for post identification.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   After successful delete redirect user to main page of module.
   */
  public function postDelete(int $id) {
    Drupal::database()->delete('ever')->condition('id', $id)->execute();
    Drupal::messenger()->addMessage($this->t('Post was deleted!'));
    return $this->redirect('ever.form');
  }

  /**
   * Method for updating the post.
   *
   * @param int $id
   *   Take parameter from route file for post identification.
   *
   * @return array
   *   Return the selected post with $id.
   */
  public function postUpdate(int $id) {
    return Drupal::formBuilder()->getForm('Drupal\ever\Form\EverForm', $id);
  }

}
