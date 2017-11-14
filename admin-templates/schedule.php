<?php
add_action( 'init', 'register_schedule' );
function register_schedule() {
    $labels = array(
        'name' => translateWord('Schedules'),
        'add_new_item' => translateWord('Add schedules'),
        'edit_item' => translateWord('Edit schedules'),
        'menu_name' => translateWord('Schedules')
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
    add_meta_box('wpt_schedule_fields', translateWord('Scheduled pages'), 'wpt_schedule_fields', 'schedule', 'normal', 'default');
}

function getScheduleItemTemplate(){
    ob_start();
    ?>
    <table class="schedule-item form-table">
        <tr>
            <th>
                <label><?php echo translateWord("Page name"); ?></label>
            </th>
            <td>
                <select>
                    <option value="-1">---</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>
                <label><?php echo translateWord("Info text"); ?></label>
            </th>
            <td>
                <textarea class="info-text"></textarea>
            </td>
        </tr>
        <tr>
            <th>
                <label><?php echo translateWord("Day to show"); ?></label>
            </th>
            <td>
                <input class="days" type="text" readonly/>
                <input type="text" class="datepicker"/>
            </td>
        </tr>
        <tr>
            <th></th>
            <td><a href="#" class="page-title-action remove-schedule-item"><?php echo translateWord("Remove"); ?></a></td>
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
    foreach($schedule as &$scheduleList){
        foreach($scheduleList as &$scheduleValue) {
            $scheduleValue = base64_decode($scheduleValue);
        }
    }
    $name = 'schedule';
    $lastIndex = 0;
    ?>
    <div class="schedule-block">
        <div class="loader-spinner">
            <div class="loader-text">
                <?php echo translateWord("Loading, wait some seconds...");?>
            </div>
            <div class="background"></div>
        </div>
        <table class="tmp-date form-table">
            <tr>
                <th>
                    <label><?php echo translateWord("Temp date start"); ?></label>
                </th>
                <td>
                    <input type="text" name="tmp-date" class="datepicker" value="<?php echo $tmpDate;?>"/>
                </td>
            </tr>
        </table>
        <div class="schedule">
        </div>
        <a href="#" class="page-title-action add-schedule-item"><?php echo translateWord("Add"); ?></a>
        <style>
            .loader-spinner {
                width: 100%;
                height: 103px;
                position: absolute;
                top: -7px;
                left: 0px;
                z-index: 2;
            }
            .loader-text{
                position: absolute;
                top: calc(50% - 10px);
                width: 100%;
                text-align: center;
                font-size: 16px;
            }
            .loader-spinner .background{
                background: #000;
                -ms-filter: "alpha(opacity=10)";
                opacity: 0.1;
                height: 100%;
            }
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
            .schedule-item.form-table .datepicker,
            .wp-admin .schedule-item.form-table select,
            .tmp-date.form-table .datepicker
            {
                width: 250px;
            }
            .schedule-item.form-table .info-text{
                margin-top: 5px;
                width: 250px;
                height: 50px;
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
                var loaderSpinner = jQuery('.loader-spinner');
                loaderSpinner.height( loaderSpinner.closest('.schedule-block').height() + 20);
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
                var lastIndex = <?php echo $lastIndex;?>;
                var name = '<?php echo $name;?>';
                var clientPages = [<?php
                    $jsClientPages = [];
                    foreach ($clientPages as $clientPage) {
                        $jsClientPages[] = '{
                            pageId: "' . $clientPage->ID . '", 
                            postTitle: "' . $clientPage->post_title . '"
                        }';
                    }
                    echo implode(',', $jsClientPages);
                ?>];
                clientPages = clientPages.sort(function (a, b) {
                    return a.postTitle.localeCompare( b.postTitle );
                });

                var scheduleTemplate = jQuery(<?php echo "'" . str_replace("\n", " ", getScheduleItemTemplate()) . "'";?>);

                <?php
                foreach($schedule as $scheduleItem){
                    $pageId = $scheduleItem['pageId'];
                    $pageDay = $scheduleItem['pageDay'];
                    $pageInfo = $scheduleItem['pageInfo'];
                    ?>
                    insertScheduleItem(scheduleTemplate, clientPages, name, <?php echo $pageId;?>, <?php echo $pageDay;?>, <?php echo "'{$pageInfo}'";?>);
                    <?php
                }
                ?>
                applyIsSelected();

                function applyIsSelected(){
                    var selectedPages = {};
                    jQuery('.schedule-item select').each(function(index, item){
                        selectedPages[jQuery(item).val()] = true;
                    });
                    jQuery.each( clientPages, function(index, clientPage){
                        if(typeof selectedPages[clientPage.pageId] != 'undefined'){
                            jQuery('.schedule-item select option[value=' + clientPage.pageId + ']:not(:selected)').hide().prop('disabled', true);
                        }
                        else{
                            jQuery('.schedule-item select option[value=' + clientPage.pageId + ']').show().prop('disabled', false);
                        }
                    });
                }

                function getFormattedDate(date) {
                    var year = date.getFullYear();
                    var month = (1 + date.getMonth()).toString();
                    month = month.length > 1 ? month : '0' + month;
                    var day = date.getDate().toString();
                    day = day.length > 1 ? day : '0' + day;
                    return year + '-' + month + '-' + day;
                }

                function insertScheduleItem(scheduleTemplate, clientPages, name, pageId, pageDay, pageInfo) {
                    var newScheduleItem = scheduleTemplate.clone();
                    var select = jQuery(newScheduleItem).find('select');
                    select.attr('name', name + '[' + lastIndex + '][pageId]');
                    jQuery.each(clientPages, function(index, clientPage){
                        var selected = (pageId == clientPage.pageId) ? 'selected' : '';
                        select.append(
                            '<option value="' + clientPage.pageId + '" ' + selected + '>' + clientPage.postTitle + '</option>'
                        );
                    });
                    var input = jQuery(newScheduleItem).find('input.days');
                    input.attr('name', name + '[' + lastIndex + '][pageDay]');
                    if(typeof pageDay == 'undefined'){
                        pageDay = 0;
                    }
                    input.val(pageDay);

                    var textArea = jQuery(newScheduleItem).find('textarea.info-text');
                    textArea.attr('name', name + '[' + lastIndex + '][pageInfo]');
                    if(typeof pageInfo != 'undefined'){
                        textArea.val(pageInfo);
                    }

                    var datepickerObject = newScheduleItem.find('.datepicker');
                    var selectedDate = new Date(jQuery('.tmp-date .datepicker').val());
                    var newDate = new Date(selectedDate);
                    newDate.setDate(newDate.getDate() + pageDay);
                    datepickerObject.val(getFormattedDate(newDate));

                    datepickerObject.datepicker({
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
                    applyIsSelected();
                }

                function getDays($milliSeconds){
                    return Math.round($milliSeconds / 24 / 3600 / 1000);
                }

                function removeScheduleItem(scheduleItem){
                    scheduleItem.remove();
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
                loaderSpinner.hide();
            });
        </script>
    </div>
    <?php
}

add_action('save_post_schedule', 'wpt_save_schedules_meta');
function wpt_save_schedules_meta($postId, $post) {
    $schedule = $_POST['schedule'];
    foreach($schedule as &$scheduleList){
        foreach($scheduleList as &$scheduleValue){
            $scheduleValue = base64_encode($scheduleValue);
        }
    }
    savePost($postId, 'schedule', serialize($schedule));
    savePost($postId, 'tmp-date', $_POST['tmp-date']);
}
