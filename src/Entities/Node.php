<?php

namespace Drupal\xtended_entity\Entities;

class Node extends \Entity {
  
  public $nid;
  
  public $vid;
  
  public $type;
  
  public $language;
  
  public $status;
  
  public $title;
  
  public $uid;
  
  public $created;
  
  public $changed;
  
  public $comment;
  
  public $promote;
  
  public $sticky;
  
//   public $translate;
  
//   public $content;
  
  public function __construct( array $values = array(), $entityType = "node" ) {
    parent::__construct( $values, $entityType );
  }
  
  public function getId() {
    return $this->nid;
  }
  
  
}