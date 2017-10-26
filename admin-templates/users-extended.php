<?php

add_action( 'show_user_profile', 'add_extra_user_fields' );
add_action( 'edit_user_profile', 'add_extra_user_fields' );

function add_extra_user_fields( $user ) {
    $posts = get_posts([
        'post_type' => 'client-groups',
    ]);
    $selectedGroupID = get_user_meta( $user->ID, 'client-group', true);
    ?>
    <select name="client-group">
        <option value="-1">
            ---
        </option>
        <?php
            foreach( $posts as $post){
                $groupID = $post->ID;
                ?>
                    <option value="<?php echo $groupID ?>" <?php
                        if ($selectedGroupID == $groupID) {
                            echo 'selected';
                        }
                    ?>>
                        <?php echo $post->post_title; ?>
                    </option>
                <?php
            }
        ?>
    </select>
    <?php
}

add_action( 'personal_options_update', 'save_extra_user_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_fields' );

function save_extra_user_fields( $userId ) {
    $key = 'client-group';
    $value = sanitize_text_field( $_POST['client-group'] );
    if(get_user_meta($userId, $key, FALSE)) {
        update_user_meta($userId, $key, $value);
    } else {
        add_user_meta($userId, $key, $value);
    }
    if(!$value){
        delete_user_meta($userId, $key);
    }
}

?>