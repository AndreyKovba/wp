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
                <input class="days" type="text" readonly/>
                <input type="text" class="datepicker"/>
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
    $tmpDate = get_post_meta($post->ID, 'tmp-date', true);
    if(!$tmpDate){
        $tmpDate = date('Y-m-d');
    }
    $schedule = unserialize(get_post_meta($post->ID, 'schedule', true));
    $name = 'schedule';
    $lastIndex = 0;
    ?>
    <div class="schedule-block">
        <table class="tmp-date form-table">
            <tr>
                <th>
                    <label>Temp date start</label>
                </th>
                <td>
                    <input type="text" name="tmp-date" class="datepicker" value="<?php echo $tmpDate;?>"/>
                </td>
            </tr>
        </table>
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
            .postbox table.form-table.tmp-date{
                margin-bottom: 30px;
            }
            .tmp-date th,
            .tmp-date td,
            .schedule-item.form-table th,
            .schedule-item.form-table td{
                position: relative;
                padding: 0px !important;
                height: 35px;
                vertical-align: middle;
            }
            .tmp-date th,
            .schedule-item.form-table th{
                line-height: 15px;
                width: 150px;
            }
            .schedule-item.form-table .days,
            .schedule-item.form-table .datepicker
            {
                width: 100px;
            }
            .schedule-item.form-table .days{
                background: white;
                cursor: text;
            }
            .schedule-item.form-table .datepicker{
                opacity: 0;
                position: absolute;
                top: 4px;
                left: 0px;
            }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function(){
                var tmpDate = jQuery('.tmp-date .datepicker').val();
                jQuery('.tmp-date .datepicker').datepicker({
                    dateFormat: 'yy-mm-dd',
                    onSelect: function(dateText, inst) {
                        var selectedTime = new Date(dateText).getTime();
                        var oldTmpTime = new Date(tmpDate).getTime();
                        var days = getDays(selectedTime - oldTmpTime);
                        tmpDate = dateText;
                        jQuery('.schedule-item .days').each(function(index){
                            var newDays = jQuery(this).val()*1 - days;
                            if(newDays<0){
                                newDays = 0;
                            }
                            jQuery(this).val(newDays);
                        });
                    },
                });
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


                var scheduleTemplate = jQuery(<?php echo "'" . str_replace("\n", " ", getScheduleItemTemplate()) . "'";?>);

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
                            '<option value="' + index + '" ' + selected + '>' + value.postTitle + '</option>'
                        );
                    });
                    var input = jQuery(newScheduleItem).find('input.days');
                    input.attr('name', name + '[' + lastIndex + '][pageDay]');
                    if(typeof pageDay == 'undefined'){
                        pageDay = 0;
                    }
                    input.val(pageDay);
                    newScheduleItem.find('.datepicker').datepicker({
                        dateFormat: 'yy-mm-dd',
                        onSelect: function(dateText, inst) {
                            var selectedTime = new Date(dateText).getTime();
                            var tmpTime = new Date(jQuery('.tmp-date .datepicker').val()).getTime();
                            var days = getDays(selectedTime - tmpTime);
                            if(days < 0){
                                days = 0;
                            }
                            jQuery('#' + inst.id).closest('.schedule-item').find('.days').val(days);
                        },
                    });
                    jQuery('.schedule').append(newScheduleItem);
                    lastIndex++;
                    currentPagesCount++;
                    if(!isCanAdd()){
                        jQuery('.add-schedule-item').hide();
                    }
                    applyIsSelected();
                }

                function getDays($milliSeconds){
                    return Math.round($milliSeconds / 24 / 3600 / 1000);
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
    savePost($postId, 'schedule', serialize($_POST['schedule']));
    savePost($postId, 'tmp-date', $_POST['tmp-date']);
}
