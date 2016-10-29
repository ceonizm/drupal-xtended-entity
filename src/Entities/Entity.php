<?php

namespace Drupal\xtended_entity\Entities;

class Entity extends \Entity {
  
  private $__values = array();
  
  /**
   * 
   * @var integer
   */
  public $id;
  
  /**
   * 
   * @var Boolean
   */
  public $is_new;
  
  /**
   * 
   * @var string
   */
  public $type;
  
  /**
   * 
   * @var string
   */
  protected $bundleKey;
  
  
  /**
   * 
   * @param array $values
   * @param string $entity_type
   */
  function __construct(array $values = array(), $entity_type ) {
    parent::__construct( $values, $entity_type );
    
    if( isset( $values[$this->bundleKey] ) ) {
      $this->type = $values[$this->bundleKey];
    }
  }
  
  /**
   * magic getter
   * try to call a getter function if exists
   * or at least a private member starting by _
   * 
   * @param string $name
   */
  public function &__get( $name ) {
    $getterCandidate = 'get'.ucfirst( $name );
     
    if( method_exists($this, $getterCandidate ) ) {
      return $this->{$getterCandidate}();
    }
    
    if( array_key_exists( $name, $this->__values) ) {
      $val = &$this->__values[$name];
      return $val;
    }
    
    $null = NULL;
    $nullRef =& $null;
    return $nullRef;
  }
  
  public function __isset( $name ) {
    $getterCandidate = 'get'.ucfirst( $name );
    if( method_exists($this, $getterCandidate ) ) {
      return TRUE;
    }
    
    if( isset( $this->__values[$name] ) ) return TRUE;

    return FALSE;
  }
  
  /**
   * magic setter
   * try to call a setter function if exists
   * or at least a private member starting by _
   *
   * @param string $name
   */
  public function __set( $name, $value ) {
     
    $setterCandidate = 'set'.ucfirst( $name );
     
    if( method_exists($this, $setterCandidate ) ) {
      return $this->{$setterCandidate}( !empty( $value ) ? $value : NULL );
    } else {
       if( is_array( $value) ) $this->__values[$name] = array_merge( isset( $this->__values[$name] ) ? $this->__values[$name] : array(), $value );
       else $this->__values[$name] = $value;      
    }
    
    return $this;
  }
  
  protected function setUp() {
    parent::setUp();
    if( isset( $this->entityInfo()['entity keys']['bundle'] ) ) {
      $this->bundleKey = $this->entityInfo()['entity keys']['bundle'];
    }
  }
  
  

  public function getType() {
    return $this->type;
  }
  
  public function setType($_type) {
    $this->type = $_type;
    return $this;
  }
  
  public function getId() {
    return $this->id;
  }
  
  public function setId($_id) {
    $this->id = $_id;
    return $this;
  }
}