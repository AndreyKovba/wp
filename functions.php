<?php
// Add your custom functions here

// Queue parent style followed by child/customized style
add_action( 'wp_enqueue_scripts', 'sparkling_enqueue_child_styles', 99);

function sparkling_enqueue_child_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_dequeue_style('sparkling-style');
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css'
    );
}

require_once $_SERVER['DOCUMENT_ROOT']. "/wp-content/themes/sparkling-child/admin-templates/client-groups.php";
require_once $_SERVER['DOCUMENT_ROOT']. "/wp-content/themes/sparkling-child/admin-templates/users-extended.php";

?>