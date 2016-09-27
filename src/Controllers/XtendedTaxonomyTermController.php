<?php

namespace Drupal\xtended_entity\Controllers;


class XtendedTaxonomyTermController extends XtendedEntityController {
  
  public function __construct( $pBundle = NULL) {
    parent::__construct("taxonomy_term", $pBundle );

  }
  
  public function retrieveTermByName( $name ) {
    return $this->retrieveEntityByProperty( 'name', $name );
  }
  
  /**
   * Borrowed from originalTaxonomyTermController
   * {@inheritDoc}
   * @see \TaxonomyTermController::buildQuery()
   */ 
  protected function buildQuery($ids, $conditions = array(), $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $conditions, $revision_id);
    $query->addTag('translatable');
    $query->addTag('term_access');
    // When name is passed as a condition use LIKE.
    if (isset($conditions['name'])) {
      $query_conditions = &$query->conditions();
      foreach ($query_conditions as $key => $condition) {
        if (is_array($condition) && $condition['field'] == 'base.name') {
          $query_conditions[$key]['operator'] = 'LIKE';
          $query_conditions[$key]['value'] = db_like($query_conditions[$key]['value']);
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
   * {@inheritDoc}
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