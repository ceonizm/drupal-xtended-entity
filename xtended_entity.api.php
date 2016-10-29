<?php

/**
 * Allows other modules to define thier own implementation of entity controller 
 * for a given entity type.
 * This hook receive an array by reference which contains:
 *    entity_type: the type of entity 
 *    ctrl: the current controller candidates
 *    
 * to be accepted as a replacement the controller implementation MUST 
 * extend the default controller class supplied by xtended_entity
 * @param unknown $message
 */
function hook_xtended_entity_choose_controller( &$message ) {
  if( $message['entity_type'] == 'taxonomy_term' ) {
    $message['ctrl'] = MySuperExtendedTaxonomyController::class;
  }
}
