<?php
add_action( 'init', 'register_schedule' );
function register_schedule() {
    $labels = array(
        'name' => 'Schedules',
        'add_new_item' => 'Add schedules',
        'edit_item' => 'Edit schedules',
        'menu_name' => 'Schedules'
    );
    $args = array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'menu_position' => 21,
        'supports' => array('title'),
        'register_meta_box_cb' => 'add_schedule_metaboxes',
    );
    register_post_type('schedule', $args);
}


function add_schedule_metaboxes() {
    add_meta_box('wpt_schedule_fields', 'Scheduled pages', 'wpt_schedule_fields', 'schedule', 'normal', 'default');
}

function getScheduleItemTemplate(){
    ob_start();
    ?>
    <table class="schedule-item form-table">
        <tr>
            <th>
                <label>Page name</label>
            </th>
            <td>
                <select>
                    <option value="-1">---</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>
                <label>Day to show</label>
            </th>
            <td>
                <input type="text"/>
            </td>
        </tr>
        <tr>
            <th></th>
            <td><a href="#" class="page-title-action remove-schedule-item">Remove</a></td>
        </tr>
    </table>
    <?php
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

function wpt_schedule_fields() {
    $clientPages = get_pages([
        'meta_key' => '_wp_page_template',
        'meta_value' => 'page-templates/client-page-template.php'
    ]);
    global $post;
    $schedule = unserialize(get_post_meta($post->ID, 'schedule', true));
    $name = 'schedule';
    $lastIndex = 0;
    ?>
    <div class="schedule-block">
        <div class="schedule">
        </div>
        <a href="#" class="page-title-action add-schedule-item">Add</a>
        <style>
            .remove-schedule-item{
                margin-left: 0px !important;
            }
            .schedule-item.form-table{
                margin-bottom: 15px !important;
            }
            .schedule-item.form-table th,
            .schedule-item.form-table td{
                padding: 0px !important;
                height: 35px;
                vertical-align: middle;
            }
            .schedule-item.form-table th{
                line-height: 15px;
                width: 150px;
            }
        </style>
        <script>
            jQuery(document).ready(function(){
                var currentPagesCount = 0;
                var lastIndex = <?php echo $lastIndex;?>;
                var name = '<?php echo $name;?>';
                var clientPages = {<?php
                    foreach ($clientPages as $clientPage) {
                        echo '"' . $clientPage->ID . '": {
                            postTitle: "' . $clientPage->post_title . '",
                        },';
                    }
                ?>};
                var scheduleTemplate = jQuery(<?php echo '`' . getScheduleItemTemplate() . '`';?>);
                <?php
                foreach($schedule as $scheduleItem){
                    $pageId = $scheduleItem['pageId'];
                    ?>
                    insertScheduleItem(scheduleTemplate, clientPages, name, <?php echo $pageId;?>, <?php echo $scheduleItem['pageDay'];?>);
                    <?php
                }
                ?>
                applyIsSelected();

                function isCanAdd(){
                    return Object.keys(clientPages).length > currentPagesCount;
                }

                function applyIsSelected(){
                    var selectedPages = [];
                    jQuery('.schedule-item select').each(function(index){
                        selectedPages[this.value] = true;
                    });
                    jQuery.each( clientPages, function(index, value){
                        if(typeof selectedPages[index] != 'undefined'){
                            jQuery('.schedule-item select option[value=' + index + ']:not(:selected)').hide();
                        }
                        else{
                            jQuery('.schedule-item select option[value=' + index + ']').show();
                        }
                    });
                }

                function insertScheduleItem(scheduleTemplate, clientPages, name, pageId, pageDay) {
                    var newScheduleItem = scheduleTemplate.clone();
                    var select = jQuery(newScheduleItem).find('select');
                    select.attr('name', name + '[' + lastIndex + '][pageId]');
                    jQuery.each(clientPages, function(index, value){
                        var selected = (pageId == index) ? 'selected' : '';
                        select.append(
                            `<option value="` + index + `" ` + selected + `>` + value.postTitle + `</option>`
                        );
                    });
                    var input = jQuery(newScheduleItem).find('input');
                    input.attr('name', name + '[' + lastIndex + '][pageDay]');
                    if(typeof pageDay == 'undefined'){
                        pageDay = 0;
                    }
                    input.val(pageDay);
                    jQuery('.schedule').append(newScheduleItem);
                    lastIndex++;
                    currentPagesCount++;
                    if(!isCanAdd()){
                        jQuery('.add-schedule-item').hide();
                    }
                    applyIsSelected();
                }

                function removeScheduleItem(scheduleItem){
                    scheduleItem.remove();
                    currentPagesCount--;
                    if(isCanAdd()){
                        jQuery('.add-schedule-item').show();
                    }
                    applyIsSelected();
                }

                jQuery(document).on('change', '.schedule-item select', function() {
                    applyIsSelected();
                });

                jQuery(document).on('change', '.schedule-item input', function() {
                    jQuery(this).val(Math.abs( parseInt(jQuery(this).val()) ));
                });

                jQuery(document).on('click', '.add-schedule-item', function (e) {
                    e.preventDefault();
                    insertScheduleItem(scheduleTemplate, clientPages, name, -1);
                });

                jQuery(document).on('click', '.remove-schedule-item', function (e) {
                    e.preventDefault();
                    removeScheduleItem(jQuery(this).closest('.schedule-item'));
                });
            });
        </script>
    </div>
    <?php
}

add_action('save_post_schedule', 'wpt_save_schedules_meta');
function wpt_save_schedules_meta($postId, $post) {
    $key = 'schedule';
    $value = serialize($_POST[$key]);
    if(get_post_meta($postId, $key, false)) {
        update_post_meta($postId, $key, $value);
    } else {
        add_post_meta($postId, $key, $value);
    }
    if(!$value){
        delete_post_meta($postId, $key);
    }
}
