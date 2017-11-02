<?php
function prepareScheduleBlockData(){
    $scheduleData = [];
    $clientPages = getClientPages(false, true);
    foreach($clientPages as $clientPage){
        if(strlen($clientPage->pageInfo)>0){
            $scheduleData[] = [
                'startDate' => $clientPage->startDate->format('Y-m-d'),
                'pageInfo' => $clientPage->pageInfo,
            ];
        }
    }
    return $scheduleData;
}

if(isset($_SESSION['client_group'])) {
    $scheduleData = prepareScheduleBlockData();
    if(count($scheduleData)>0) {
        ?>
        <div class="col-sm-12 col-md-4">
            <div class="well">
                <?php
                foreach ($scheduleData as $scheduleDataItem) {
                    ?>
                    <div>
                        <?php echo $scheduleDataItem['startDate']; ?>:
                        <?php echo $scheduleDataItem['pageInfo']; ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }
}