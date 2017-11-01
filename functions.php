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

function getClientPages($ignoreDate = false){
    if(!isset($_SESSION['client_group'])){
        return [];
    }
    $clientGroup = get_post($_SESSION['client_group']);
    if(!$clientGroup){
        return [];
    }
    $allPages = get_pages([
        'meta_key' => '_wp_page_template',
        'meta_value' => 'page-templates/client-page-template.php'
    ]);
    $scheduleInfo = [];
    $currentDate = new DateTime();
    $startDate = DateTime::createFromFormat ('Y-m-d', get_post_meta($clientGroup->ID, 'start-date', true));
    $scheduleId = get_post_meta($clientGroup->ID, 'schedule', true);
    $scheduleData = unserialize(get_post_meta($scheduleId, 'schedule', true));
    foreach($scheduleData as $scheduleItem){
        $scheduleStartDate = clone($startDate);
        $scheduleStartDate->add(new DateInterval("P{$scheduleItem['pageDay']}D"));
        $scheduleInfo[$scheduleItem['pageId']] = [
            'startDate' => $scheduleStartDate,
            'isAvailable' => ($scheduleStartDate <= $currentDate),
        ];
    }
    return array_filter(
        $allPages,
        function($page, $index) use ($scheduleInfo, $ignoreDate) {
            if(isset($scheduleInfo[$page->ID])){
                $page->startDate = $scheduleInfo[$page->ID]['startDate'];
                return $ignoreDate || $scheduleInfo[$page->ID]['isAvailable'];
            }
            return true;
        }
    );
}

function getAvailablePagesIds($availablePages){
    $availablePagesIds = [];
    foreach($availablePages as $availablePage){
        $availablePagesIds[] = $availablePage->ID;
    }
    return $availablePagesIds;
}

function getAvailableMenuItems($availablePages){
    $availableMenuItems = [];
    $menuItems = wp_get_nav_menu_items('Main');
    foreach($menuItems as $menuItem){
        if( in_array($menuItem->object_id, $availablePages) ){
            $availableMenuItems[] = $menuItem->ID;
        }
    }
    return $availableMenuItems;
}

add_action('wp_ajax_get_month', 'get_month_callback');
add_action('wp_ajax_nopriv_get_month', 'get_month_callback');
function get_month_callback() {
    $monthes = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    $year = $_POST['year'] * 1;
    $month = $_POST['month'] * 1;
    $pagesDates = [];
    $currentDate = new DateTime();
    if(isset($_POST['pagesDates']) && isset($_POST['pagesDates']["{$year}-{$month}"])){
        $pagesDates = $_POST['pagesDates'][$year.'-'.$month];
    }
    ?>

    <div class="month-block">
        <a href="#" class="previous-month"> < </a>
        <?php echo $monthes[$month - 1] . ' ' . $year; ?>
        <a href="#" class="next-month"> > </a>
    </div>
    <?php
    $skipDays = (date('w', strtotime("{$year}-{$month}-01")) + 6) % 7;
    $maxDate = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $isFuture = false;
    ?>
    <table class="days-list">
    <?php
        $dayNumber = 1;
        while($dayNumber <= $maxDate) {
            ?>
            <tr>
                <?php
                for($i=0; $i<7; $i++) {
                    ?>
                    <td>
                        <?php
                        if($skipDays>0){
                            $skipDays--;
                        }
                        else if ($dayNumber <= $maxDate){
                            $cellClass = '';
                            if(in_array($dayNumber, $pagesDates)){
                                $cellClass = 'open-date';
                                if(!$isFuture){
                                    $cellDate = DateTime::createFromFormat ('Y-m-d', "{$year}-{$month}-{$dayNumber}");
                                    $isFuture = $cellDate > $currentDate;
                                }

                                if($isFuture){
                                    $cellClass .= ' future';
                                }
                            }
                            ?>
                            <div class="day-number <?php echo $cellClass;?>">
                                <?php echo $dayNumber;?>
                            </div>
                            <?php
                            $dayNumber++;
                        }
                        ?>
                    </td>
                    <?php
                }
                ?>
            </tr>
            <?php
        }
    ?>
    </table>
    <?php
    wp_die();
}

function savePost($postId, $key, $value){
    if(get_post_meta($postId, $key, false)) {
        update_post_meta($postId, $key, $value);
    } else {
        add_post_meta($postId, $key, $value);
    }
    if(!$value){
        delete_post_meta($postId, $key);
    }
}

require_once $_SERVER['DOCUMENT_ROOT']. "/wp-content/themes/sparkling-child/admin-templates/client-groups.php";
require_once $_SERVER['DOCUMENT_ROOT']. "/wp-content/themes/sparkling-child/admin-templates/users-extended.php";
require_once $_SERVER['DOCUMENT_ROOT']. "/wp-content/themes/sparkling-child/admin-templates/schedule.php";
?>