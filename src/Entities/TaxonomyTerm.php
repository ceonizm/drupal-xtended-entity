<?php

namespace Drupal\xtended_entity\Entities;

class TaxonomyTerm extends Entity {
  
  public $tid;
  
  public $vocabulary_machine_name;
  
  public $vid;
  
  public $name;
  
  public $language;
  
  public $description;
  
  public $format;
  
  public $weight;
  
  public $created;
    
  public $changed;
  
  public function __construct( array $values = array(), $entityType = "taxonomy_term" ) {
    parent::__construct( $values, $entityType );
  }
  
//   public function __get( $name ) {
//     debug( "__get ( $name )");
//     return parent::__get( $name );
//   }
  
//   public function __set( $name, $value ) {
//     debug( "__set( $name, ".var_export( $value, 1)." )" );
//     parent::__set($name, $value);
    
//     return $this;
//   }
}