<?php

namespace Drupal\adyax_ws\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller routines for REST resources.
 */
class AdyaxController extends ControllerBase {

  /**
   * Method to get node information.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Node information in json format.
   */
  public function get(Request $request) {
    // Check is required nid parameter exist.
    if (!($nid = $request->get('nid'))) {
      return new JsonResponse($this->t('Missing required parameter nid.'), 404);
    }
    /** @var \Drupal\node\NodeInterface $node */
    elseif (!($node = $this->entityTypeManager()->getStorage('node')->load($nid))) {
      return new JsonResponse($this->t('Node with nid @nid not found.', ['@nid' => $nid]), 404);
    }

    return new JsonResponse($node->toArray());
  }

  /**
   * Helper function to load node type.
   *
   * @param string $id
   *   Node type id to load.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   An entity object. NULL if no matching entity is found.
   */
  protected function checkNodeType($id) {
    return $this->entityTypeManager()->getStorage('node_type')->load($id);
  }

  /**
   * Method to create new node.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Status text in json format.
   */
  public function post(Request $request) {
    // Get json data and decode to array.
    $data = Json::decode($request->getContent());
    // Check is required type parameter exist.
    if (!isset($data['type'])) {
      return new JsonResponse($this->t('Missing required parameter type.'), 404);
    }
    // Check is node type exist in system.
    elseif (!$this->checkNodeType($data['type'])) {
      return new JsonResponse($this->t('Invalid node type.'), 404);
    }
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->entityTypeManager()->getStorage('node')->create([
      'title' => $data['title'],
      'type' => $data['type'],
      'body' => $data['body'],
    ]);
    $node->save();

    return new JsonResponse($this->t('Node with nid @id was created.', ['@id' => $node->id()]));
  }

  /**
   * Method to update node.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Status text in json format.
   */
  public function put(Request $request) {
    // Get json data and decode to array.
    $data = Json::decode($request->getContent());
    // Check is required nid parameter exist.
    if (!isset($data['nid'])) {
      return new JsonResponse($this->t('Missing required parameter nid.'), 404);
    }
    /** @var \Drupal\node\NodeInterface $node */
    elseif (!($node = $this->entityTypeManager()->getStorage('node')->load($data['nid']))) {
      return new JsonResponse($this->t('Node with nid @nid not found.', ['@nid' => $data['nid']]), 404);
    }
    // Remove nid parameter.
    unset($data['nid']);
    foreach ($data as $field => $value) {
      if ($node->hasField($field)) {
        $node->set($field, $value);
      }
    }
    $node->save();

    return new JsonResponse($this->t('Node with nid @id was updated.', ['@id' => $node->id()]));
  }

  /**
   * Method to remove node.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Status text in json format.
   */
  public function delete(Request $request) {
    // Check is required nid parameter exist.
    if (!($nid = $request->get('nid'))) {
      return new JsonResponse($this->t('Missing required parameter nid.'), 404);
    }
    /** @var \Drupal\node\NodeInterface $node */
    elseif (!($node = $this->entityTypeManager()->getStorage('node')->load($nid))) {
      return new JsonResponse($this->t('Node with nid @nid not found.', ['@nid' => $nid]), 404);
    }
    $node->delete();

    return new JsonResponse($this->t('Node with nid @id was deleted.', ['@id' => $nid]));
  }

}
