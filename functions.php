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

function getClientPages($ignoreDate = false, $getInfo = false){
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
    $fakePageId = -2;
    foreach($scheduleData as $scheduleItem){
        $scheduleStartDate = clone($startDate);
        $scheduleStartDate->add(new DateInterval("P{$scheduleItem['pageDay']}D"));
        $pageId = $scheduleItem['pageId'];
        if($getInfo && $pageId==-1){
            $pageId = $fakePageId;
            $fakePage = new stdClass();
            $fakePage->ID = $pageId;
            $allPages[] = $fakePage;
            $fakePageId--;
        }
        $scheduleInfo[$pageId] = [
            'startDate' => $scheduleStartDate,
            'isAvailable' => (bool) ($scheduleStartDate <= $currentDate),
            'pageInfo' => $scheduleItem['pageInfo'],
        ];
    }
    return array_filter(
        $allPages,
        function($page, $index) use ($scheduleInfo, $ignoreDate, $startDate) {
            if(isset($scheduleInfo[$page->ID])){
                $page->startDate = $scheduleInfo[$page->ID]['startDate'];
                $page->pageInfo = $scheduleInfo[$page->ID]['pageInfo'];
                return $ignoreDate || $scheduleInfo[$page->ID]['isAvailable'];
            }
            $page->startDate = $startDate;
            $page->pageInfo = '';
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

function sortClientPagesByDate($clientPages, $backward = false){
    uasort($clientPages, function($firstElement, $secondElement) use($backward){
        if(!isset($firstElement->startDate)){
            return $backward ? 1 : -1;
        }
        if(!isset($secondElement->startDate)){
            return $backward ? -1 : 1;
        }
        return
            $firstElement->startDate > $secondElement->startDate ?
                ($backward ? -1 : 1) : ($backward ? 1 : -1);
    });
    return $clientPages;
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
    if( isset($_POST['pagesDatesData']) && isset($_POST['pagesDatesData']["{$year}-{$month}"]) ) {
        foreach ($_POST['pagesDatesData']["{$year}-{$month}"] as $day=>$info) {
            $pagesDates[$day*1] = $info;
        }
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
                            if(isset($pagesDates[$dayNumber])){
                                $cellClass = 'open-date';
                                if(!$isFuture){
                                    $cellDate = DateTime::createFromFormat ('Y-m-d', "{$year}-{$month}-{$dayNumber}");
                                    $isFuture = $cellDate > $currentDate;
                                }

                                if($isFuture){
                                    $cellClass .= ' future';
                                }

                                $infoText = "";
                                if(strlen($pagesDates[$dayNumber])>0){
                                    $cellClass .= ' has-info';
                                    $infoText = $pagesDates[$dayNumber];
                                }
                            }
                            ?>
                            <div class="day-number <?php echo $cellClass;?>" rel="">
                                <div class="inner-text-template" style="display: none"><?php
                                    echo html_entity_decode($infoText);
                                ?></div>
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

function translateWord($word){
    if(get_locale() != 'sv_SE'){
        return $word;
    }
    $translations = [
        "Client groups" => "Rehabgrupper",
        "Edit client group" => "Redigera rehabgrupp",
        "Add client group" => "Lägg till rehabgrupp",
        "Group fields" => "Rehabgrupp",
        "Password" => "Lösenord",
        "Start date" => "Start datum",
        "Client Page Template" => "Mina sidor rehabgrupp",

        "Schedules" => "Gruppscheman",
        "Edit schedules" => "Redigera gruppschema",
        "Add schedules" => "Lägg till gruppschema",
        "Scheduled pages" => "Gruppschema",
        "Temp date start" => "Datum för utgångspunkt",
        "Page name" => "Sidnamn",
        "Info text" => "Händelse",
        "Day to show" => "Visningsdag",
        "Add" => "Lägg till",
        "Remove" => "Ta bort",
    ];
    if(!isset($translations[$word])){
        return $word;
    }
    return $translations[$word];
}

require_once $_SERVER['DOCUMENT_ROOT']. "/wp-content/themes/sparkling-child/admin-templates/client-groups.php";
require_once $_SERVER['DOCUMENT_ROOT']. "/wp-content/themes/sparkling-child/admin-templates/users-extended.php";
require_once $_SERVER['DOCUMENT_ROOT']. "/wp-content/themes/sparkling-child/admin-templates/schedule.php";
?>