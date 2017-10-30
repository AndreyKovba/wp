<?php
add_action( 'init', 'register_client_group_type' );
function register_client_group_type() {
    $labels = array(
        'name' => 'Client groups',
        'add_new_item' => 'Add client group',
        'edit_item' => 'Edit client group',
        'menu_name' => 'Client groups'
    );
    $args = array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'menu_position' => 20,
        'supports' => array('title'),
        'register_meta_box_cb' => 'add_events_metaboxes',
    );
    register_post_type('client-groups', $args);
}


$fields = [
    [
        'type' => 'text',
        'name' => 'password',
        'title' => 'Password',
        'class' => 'widefat',
    ],
    [
        'type' => 'text',
        'name' => 'start-date',
        'title' => 'Start date',
        'class' => 'datepicker widefat',
    ]
];

function add_events_metaboxes() {
    add_meta_box('wpt_group_fields', 'Group fields', 'wpt_group_fields', 'client-groups', 'normal', 'default');
}

wp_enqueue_style( 'jquery-ui-datepicker-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css');
wp_enqueue_script( 'jquery-ui-datepicker' );
function wpt_group_fields() {
    global $post;
    global $fields;
    foreach ($fields as $field) {
        $value = get_post_meta($post->ID, $field['name'], true);
        ?>
        <p>
            <label for="password"><strong><?php echo $field['title']; ?></strong>:</label>
            <input class="<?php echo $field['class']; ?>"
                   type="<?php echo $field['type']; ?>"
                   name="<?php echo $field['name']; ?>"
                   value="<?php echo $value; ?>" />
        </p>
        <?php
    }
    ?>
    <p>
        <select name="schedule">
            <option value="-1">---</option>
            <?php
            $schedules = get_posts([
                'post_type' => 'schedule',
            ]);
            $scheduleId = get_post_meta($post->ID, 'schedule', true);
            foreach ($schedules as $schedule){
                ?>
                <option value="<?php echo $schedule->ID;?>" <?php
                    if($scheduleId==$schedule->ID){
                        echo 'selected';
                    }
                ?>><?php
                    echo $schedule->post_title;
                    ?></option>
                <?php
            }
            ?>
        </select>
    </p>
    <script>
        jQuery(document).ready(function(){
            jQuery('.datepicker').datepicker();
        });
    </script>
    <?php
}

add_action('save_post_client-groups', 'wpt_save_events_meta');
function wpt_save_events_meta($postId, $post) {
    global $fields;
    if( $post->post_type == 'revision' ) {
        return;
    }
    if ( !current_user_can( 'edit_post', $postId )) {
        return $postId;
    }
    foreach ($fields as $field) {
        $events_meta[$field['name']] = sanitize_text_field($_POST[$field['name']]);
    }
    $events_meta['schedule'] = sanitize_text_field($_POST['schedule']);

    foreach ($events_meta as $key => $value) {
        if(get_post_meta($postId, $key, false)) {
            update_post_meta($postId, $key, $value);
        } else {
            add_post_meta($postId, $key, $value);
        }
        if(!$value){
            delete_post_meta($postId, $key);
        }
    }

}
