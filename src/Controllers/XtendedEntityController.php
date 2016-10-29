<?php

namespace Drupal\xtended_entity\Controllers;

class XtendedEntityController extends \EntityAPIControllerExportable {
  
  protected $bundle;
  
  protected static $__bundleMap = array();

  /**
   * 
   * @param string $entityType
   * @param string $bundle
   */
  public function __construct($entityType, $bundle = NULL) {
    parent::__construct($entityType);
    $this->bundle = $bundle;
    if (isset($this->entityInfo['entity keys']['bundle'])) {
      $this->bundleKey = $this->entityInfo['entity keys']['bundle'];
    }
    // auto discovering declared bundle classes
    foreach ($this->entityInfo['bundles'] as $bundleName => $bundleSpecs) {
      if (array_key_exists('bundle class', $bundleSpecs)) {
        self::registerBundleClass($this->entityType, $bundleName, $bundleSpecs['bundle class']);
      }
    }
  }

  /**
   *
   * @return EntityFieldQuery
   */
  public function buildEntityFieldQuery() {
    $efq = new \EntityFieldQuery();
    $efq->entityCondition( 'entity_type', $this->entityType );
    if( !empty( $this->bundle ) ) $efq->entityCondition( 'bundle', $this->bundle );
    return $efq;
  }

  /**
   * 
   * @return unknown
   */
  public function count() {
    $q = $this->buildEntityFieldQuery()->count();
  
    return $q->execute();
  }
  
  /**
   * 
   * @param unknown $start
   * @param unknown $amount
   * @return mixed[]|Array|The
   */
  public function loadRange($start, $amount) {
    $res = $this->buildEntityFieldQuery()->range($start, $amount)->execute();
    if ($res && !empty($res[$this->entityType])) {
      return $this->load(array_keys($res[$this->entityType]));
    }
  }
  
  /**
   *
   * @param string $pPropertyName        
   * @param string|array $pValue        
   * @param string $pColumn
   *        return array|NULL
   */
  public function retrieveByProperty($pPropertyName, $pValue = NULL, $operator = '=') {
    if (is_array($pValue)) {
      $operator = 'IN';
    }
    $res = $this->buildEntityFieldQuery()->propertyCondition($pPropertyName, $pValue, $operator)->execute();
    if ($res && isset($res[$this->entityType])) {
      return $this->load(array_keys($res[$this->entityType]));
    }
    return NULL;
  }

  /**
   *
   * @param string $pPropertyName        
   * @param mixed $pValue        
   * @param string $pOperator        
   * @return stdClass|NULL
   */
  public function retrieveEntityByProperty($pPropertyName, $pValue, $pOperator = '=') {
    $res = $this->retrieveByProperty($pPropertyName, $pValue, $pOperator);
    if ($res) {
      return reset($res);
    }
  }

  /**
   *
   * @param string $pFieldName        
   * @param string|array $pValue        
   * @param string $pColumn        
   */
  public function retrieveByField($pFieldName, $pColumn = 'value', $pValue = NULL, $operator = '=', $delta_group = NULL, $language_group = NULL) {
    if (is_array($pValue)) {
      $operator = 'IN';
    }
    $res = $this->buildEntityFieldQuery()->fieldCondition($pFieldName, $pColumn, $pValue, $operator, $delta_group, $language_group)->execute();
    if ($res && isset($res[$this->entityType])) {
      return entity_load($this->entityType, array_keys($res[$this->entityType]));
    }
    return NULL;
  }

  /**
   *
   * @param string $pFieldName        
   * @param string $pColumn        
   * @param mixed $pValue        
   * @param string $operator        
   * @param interger $delta_group        
   * @param string $language_group        
   * @return mixed|NULL
   */
  public function retrieveEntityByField($pFieldName, $pColumn = 'value', $pValue = NULL, $operator = '=', $delta_group = NULL, $language_group = NULL) {
    $res = $this->retrieveByField($pFieldName, $pColumn, $pValue, $operator, $delta_group, $language_group);
    if (!empty($res)) {
      return reset($res);
    }
    return NULL;
  }

  /**
   * getter for bundle
   */
  public function getBundle() {
    return $this->bundle;
  }

  /**
   * setter for bundle
   * 
   * @param string $bundle        
   * @return \Drupal\zjm_player\Controller\XtendedEntityController
   */
  public function setBundle($bundle) {
    $this->bundle = $bundle;
    return $this;
  }

  protected function attachLoad(&$queried_entities, $revision_id = FALSE) {
    
    parent::attachLoad($queried_entities, $revision_id);

    foreach ( $queried_entities as $entity ) {
      if (method_exists($entity, 'attachLoad')) {
        $entity->attachLoad();
      }
    }
  }

  public function create(array $values = array()) {
    $values += array(
      'is_new' => TRUE
    );
    watchdog("xtended_entity", "$this->bundleKey  is ".$this->bundleKey );
    $bundleKey = isset($this->bundle) ? $this->bundle : "";
    if (array_key_exists($this->bundleKey, $values)) {
      $bundleKey = $values[$this->bundleKey];
    }
    
    if (!empty($bundleKey)) {
      watchdog("xtended_entity", "bundleKey to lookup is ".$bundleKey );
      $class = $this->getBundleClass($bundleKey);
      watchdog("xtended_entity", "class found is ".$class );
    }
    if (empty($class)) {
      return parent::create($values);
    }
    return new $class($values);
  }

  public static function registerBundleClass($entityType, $bundle, $class) {
    if (!class_exists($class, TRUE))
      throw new \Exception("class $class doesn't exists");
    if (!array_key_exists($entityType, self::$__bundleMap))
      self::$__bundleMap[$entityType] = array();
    if( !array_key_exists( $bundle, self::$__bundleMap[$entityType]) ) {
      watchdog("xtended_entity", "registering for {$entityType}:$bundle -> $class ");
      self::$__bundleMap[$entityType][$bundle] = $class;
    }
  }

  public function getBundleClass($bundle) {
//     if (!isset($this->entityInfo['bundles'][$bundle]))
//       throw new \Exception("bundle $bundle not declared for {$this->entityType} " . var_export(array_keys($this->entityInfo['bundles']), 1));
      if( isset( self::$__bundleMap[$this->entityType][$bundle] ) ) {
        return self::$__bundleMap[$this->entityType][$bundle];
      }
      return NULL;
  }

  /**
   * override of query method
   * we'll try to lookup if a bundle class exists and set fetch mode with it
   * instead of
   * base entity class
   * 
   * {@inheritdoc}
   *
   * @see EntityAPIController::query()
   */
  public function query($ids, $conditions, $revision_id = FALSE) {
    /* @var \DatabaseStatementInterface $result */
    $result = parent::query($ids, $conditions, $revision_id);
    $results = array();
    while ($row = $result->fetchAssoc()) {
      $row['is_new'] = FALSE;

      $results[] = $this->create((array) $row);
    }

    return $results;
  }

}