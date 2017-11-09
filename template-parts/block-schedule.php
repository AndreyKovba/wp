<?php
function prepareScheduleBlockData(){
    $scheduleData = [];
    $clientPages = sortClientPagesByDate(getClientPages(true, true));
    $currentDate = new DateTime();
    foreach($clientPages as $clientPage){
        if(strlen($clientPage->pageInfo)>0){
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
                <?php
                foreach ($scheduleData as $scheduleDataItem) {
                    ?>
                    <div>
                        <?php
                        if( $scheduleDataItem['isAvailable'] && $scheduleDataItem['ID'] > 0 ) {
                            ?>
                            <a class="client-page-title" href="<?php echo get_permalink($scheduleDataItem['ID']); ?>">
                                <?php echo $scheduleDataItem['startDate'];?>:
                            </a>
                            <?php
                        }
                        else{
                            echo "{$scheduleDataItem['startDate']}: ";
                        }
                        ?>
                        <?php echo $scheduleDataItem['pageInfo']; ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        <?php
    }
}