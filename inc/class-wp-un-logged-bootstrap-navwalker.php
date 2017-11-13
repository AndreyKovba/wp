<?php
class WP_Bootstrap_Un_Logged_Navwalker extends WP_Bootstrap_Navwalker {
    public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
        if($element->ID == 859) {
            $children_elements = [];
        }
        parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
    }
}