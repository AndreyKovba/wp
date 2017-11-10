<?php
function prepareScheduleBlockData(){
    $scheduleData = [];
    $clientPages = sortClientPagesByDate(getClientPages(true, true, true));
    $currentDate = new DateTime();
    foreach($clientPages as $clientPage){
        if($clientPage->ID>0 || strlen($clientPage->pageInfo)>0) {
            $scheduleData[] = [
                'ID' => $clientPage->ID,
                'postTitle' => $clientPage->post_title,
                'startDate' => $clientPage->startDate->format('Y-m-d'),
                'pageInfo' => $clientPage->pageInfo,
                'isAvailable' => (bool) ($clientPage->startDate <= $currentDate),
            ];
        }
    }
    return $scheduleData;
}

if(isset($_SESSION['client_group'])) {
    $scheduleData = prepareScheduleBlockData();
    if(count($scheduleData)>0) {
        ?>
            <div class="well">
                <div class="widget">
                    <h3 class="widget-title">SCHEMALAGDA HÄNDELSER</h3>
                    <?php
                    foreach ($scheduleData as $scheduleDataItem) {
                        $pageInfo = strlen($scheduleDataItem['pageInfo'])>0 ? $scheduleDataItem['pageInfo'] : $scheduleDataItem['postTitle'];
                        $scheduleText = "<strong>{$scheduleDataItem['startDate']}</strong>: {$pageInfo}";
                        ?>
                        <div>
                            <?php
                            if( $scheduleDataItem['isAvailable'] && $scheduleDataItem['ID'] > 0 ) {
                                ?>
                                <a class="client-page-title" href="<?php echo get_permalink($scheduleDataItem['ID']); ?>">
                                    <?php echo $scheduleText;?>
                                </a>
                                <?php
                            }
                            else{
                                echo $scheduleText;
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        <?php
    }
}