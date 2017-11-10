<?php
add_action( 'init', 'register_client_group_type' );
function register_client_group_type() {
    $labels = array(
        'name' => translateWord('Client groups'),
        'add_new_item' => translateWord('Add client group'),
        'edit_item' => translateWord('Edit client group'),
        'menu_name' => translateWord('Client groups'),
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
        'title' => translateWord('Password'),
        'class' => 'widefat',
    ],
    [
        'type' => 'text',
        'name' => 'start-date',
        'title' => translateWord('Start date'),
        'class' => 'datepicker widefat',
    ]
];

function add_events_metaboxes() {
    add_meta_box('wpt_group_fields', translateWord('Group fields'), 'wpt_group_fields', 'client-groups', 'normal', 'default');
}

wp_enqueue_style( 'jquery-ui-datepicker-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css');
wp_enqueue_script( 'jquery-ui-datepicker' );
function wpt_group_fields() {
    global $post;
    global $fields;
    foreach ($fields as $field) {
        $value = esc_attr(get_post_meta($post->ID, $field['name'], true));
        ?>
        <p>
            <label><strong><?php echo $field['title']; ?></strong>:</label>
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
            $scheduleId = esc_attr(get_post_meta($post->ID, 'schedule', true));
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
            jQuery('.datepicker').datepicker({ dateFormat: 'yy-mm-dd'});
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
        if($key=='password'){
            $value = makeUnique($postId, $key, $value);
        }
        savePost($postId, $key, $value);
    }
}

function makeUnique($postId, $key, $value){
    global $wpdb;
    $hasErrors = false;
    $isUnique = false;
    $counter = 0;
    while(!$isUnique && $counter<100) {
        $exists = $wpdb->get_col($wpdb->prepare(
            "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id != %s AND meta_key = %s AND meta_value = %s",
            $postId, $key, $value
        ));

        if (!empty($exists)) {
            $hasErrors = true;
            $value = generateRandomString();
        }
        else{
            $isUnique = true;
        }
        $counter++;
    }
    if($hasErrors){
        $error = new WP_Error(1001, "Field \"{$key}\" should be unique! Unique value generated.");
        set_transient("my_save_post_errors", $error);
    }
    return $value;
}

function generateRandomString($length = 10) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function my_error_notice() {
    $my_save_post_errors = get_transient("my_save_post_errors");
    if(is_a($my_save_post_errors, "WP_Error")) {
        ?>
        <div class="notice-warning notice">
            <p><?php _e($my_save_post_errors->get_error_message(), 'my_plugin_textdomain'); ?></p>
        </div>
        <?php
        delete_transient('my_save_post_errors');
    }
}
add_action( 'admin_notices', 'my_error_notice' );