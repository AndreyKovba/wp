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

function login_to_group($postData){
    $groups = get_posts([
        'post_type' => 'client-groups',
        'meta_query' => [
            [
                'key' => 'password',
                'value' => $postData['group-password'],
                'compare' => '=',
            ],
        ],
    ]);
    $url = '/';
    if(count($groups)){
        $_SESSION['client_group'] = $groups[0]->ID;
        $url = site_url() . '/welcome-page/';
    }
    else{
        unset($_SESSION['client_group']);
        $_SESSION['bad-group-password'] = true;
        if(isset($postData['source_url'])) {
            $url = $postData['source_url'];
        }
    }
    header('Location: ' . $url);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT']. "/wp-content/themes/sparkling-child/admin-templates/client-groups.php";
require_once $_SERVER['DOCUMENT_ROOT']. "/wp-content/themes/sparkling-child/admin-templates/users-extended.php";
require_once $_SERVER['DOCUMENT_ROOT']. "/wp-content/themes/sparkling-child/admin-templates/schedule.php";

?>