<?php

namespace Drupal\xtended_entity\Controllers;

class XtendedTaxonomyTermController extends XtendedEntityController {

  public function __construct($entity_type = "taxonomy_term", $pBundle = NULL) {
    parent::__construct("taxonomy_term", $pBundle);
    // watchdog( "xtended_entity", "XtendedTaxonomyTermController::__construct
  // ". var_export( $this->entityInfo['bundles'], 1) );
  }

  public function create(array $values = array()) {
    /* @var $term \Drupal\xtended_entity\Entities\TaxonomyTerm */
    $term = parent::create($values);
    $voc = NULL;
    if (array_key_exists('vid', $values)) {
      $voc = taxonomy_vocabulary_load($values['vid']);
    }
    else 
      if (array_key_exists('vocabulary_machine_name', $values)) {
        $voc = taxonomy_vocabulary_machine_name_load($values['vocabulary_machine_name']);
      }
    if ($voc) {
      $term->vid = $voc->vid;
      $term->vocabulary_machine_name = $voc->machine_name;
    }
    
    return $term;
  }

  public function retrieveTermByName($name) {
    return $this->retrieveEntityByProperty('name', $name);
  }

  /**
   * Borrowed from originalTaxonomyTermController
   * 
   * {@inheritdoc}
   *
   * @see \TaxonomyTermController::buildQuery()
   */
  protected function buildQuery($ids, $conditions = array(), $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $conditions, $revision_id);
    $query->addTag('translatable');
    $query->addTag('term_access');
    $query_conditions = &$query->conditions();
    // When name is passed as a condition use LIKE.
    if (isset($conditions['name'])) {
      
      foreach ($query_conditions as $key => $condition) {
        if (is_array($condition) && $condition['field'] == 'base.name') {
          $query_conditions[$key]['operator'] = 'LIKE';
          $query_conditions[$key]['value'] = db_like($query_conditions[$key]['value']);
        }
      }
    }
    // when vocabulary_machine_name is passed don't use base
    if (isset($conditions[$this->bundleKey])) {
      
      foreach ($query_conditions as $key => $condition) {
        if (is_array($condition) && $condition['field'] == 'base.' . $this->bundleKey) {
          $query_conditions[$key]['field'] = "v.machine_name";
        }
      }
    }
    // Add the machine name field from the {taxonomy_vocabulary} table.
    $query->innerJoin('taxonomy_vocabulary', 'v', 'base.vid = v.vid');
    $query->addField('v', 'machine_name', 'vocabulary_machine_name');
    
    return $query;
  }

  /**
   * Borrowed from originalTaxonomyTermController
   * 
   * {@inheritdoc}
   *
   * @see \TaxonomyTermController::buildQuery()
   */
  protected function cacheGet($ids, $conditions = array()) {
    $terms = parent::cacheGet($ids, $conditions);
    // Name matching is case insensitive, note that with some collations
    // LOWER() and drupal_strtolower() may return different results.
    foreach ($terms as $term) {
      $term_values = (array) $term;
      if (isset($conditions['name']) && drupal_strtolower($conditions['name'] != drupal_strtolower($term_values['name']))) {
        unset($terms[$term->tid]);
      }
    }
    return $terms;
  }

}