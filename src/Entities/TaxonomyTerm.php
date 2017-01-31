<?php

namespace Drupal\xtended_entity\Entities;

class TaxonomyTerm extends Entity {
  
  const EntityType = "taxonomy_term";
  
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
  
  public $parent;
  
  
  /**
   * 
   * @var TaxonomyTerm[]
   */
  protected $_childs = array();
  
  public function __construct( array $values = array(), $entityType = "taxonomy_term" ) {
    parent::__construct( $values, $entityType );
//     $this->parent = array();
  }
  
  /**
   * 
   * @param TaxonomyTerm $term
   */
  public function addChild( $term ) {
    if( !in_array( $term, $this->_childs) ) {
      $this->_childs[] = $term;
      if( !isset( $term->parent ) ) $term->parent = array();
//       if( !in_array( $this->tid, $term->parent) ) {
        $term->parent = $this->tid;
//       }
    }
  }

}