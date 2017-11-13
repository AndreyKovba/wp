<?php
function getPageInfo($clientPage){
    return strlen($clientPage->pageInfo) > 0 ? $clientPage->pageInfo : $clientPage->post_title;
}

if(isset($_SESSION['client_group'])) {
    $clientPagesByDates = sortClientPagesByDates(getClientPages(true, true, true));
    $currentDate = new DateTime();
    if(count($clientPagesByDates)>0) {
        ?>
            <div class="well">
                <div class="widget">
                    <h3 class="widget-title">SCHEMALAGDA HÄNDELSER</h3>
                    <?php
                    foreach ($clientPagesByDates as $clientPages) {
                        uasort($clientPages, function($firstElement, $secondElement) {
                            $firstInfo = mb_strtolower(getPageInfo($firstElement));
                            $secondInfo = mb_strtolower(getPageInfo($secondElement));
                            return strcmp($firstInfo, $secondInfo);
                        });
                        foreach ($clientPages as $clientPagesItem) {
                            $isAvailable = $clientPagesItem->startDate <= $currentDate;
                            $pageInfo = stripslashes(getPageInfo($clientPagesItem));
                            $scheduleText = "<strong>{$clientPagesItem->startDate->format('Y-m-d')}</strong>: {$pageInfo}";
                            ?>
                            <div>
                                <?php
                                if ($isAvailable && $clientPagesItem->ID > 0) {
                                    ?>
                                    <a class="client-page-title" href="<?php echo get_permalink($clientPagesItem->ID); ?>">
                                        <?php echo $scheduleText; ?>
                                    </a>
                                    <?php
                                } else {
                                    echo $scheduleText;
                                }
                                ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        <?php
    }
}