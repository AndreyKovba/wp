<div class="info-text" style="display: none"></div>
<?php
add_action('wp_footer', 'my_action_javascript', 99); // для фронта
function my_action_javascript() {
    $clientPages = getClientPages(true, true);
    $pagesDates = [];
    $currentDate = new DateTime();
    foreach($clientPages as $clientPage){
        if(isset($clientPage->startDate)) {
            $startDate = $clientPage->startDate;
            $yearAndMonth = $startDate->format('Y-m');
            $day = $startDate->format('d');
            if(!isset($pagesDates[$yearAndMonth])){
                $pagesDates[$yearAndMonth] = [];
            }
            if(!isset($pagesDates[$yearAndMonth][$day])){
                $pagesDates[$yearAndMonth][$day] = '';
            }
            if(isset($clientPage->pageInfo) && strlen($clientPage->pageInfo)>0){
                $text = $clientPage->pageInfo;
                if($clientPage->startDate <= $currentDate && $clientPage->ID > 0 ) {
                    $href = get_permalink($clientPage->ID);
                    $text = "<a href=\"{$href}\">{$clientPage->pageInfo}</a>";
                }
                $pagesDates[$yearAndMonth][$day] .= htmlentities("<p>{$text}</p>");
            }
        }
    }
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery(document).on('click', '.day-number.has-info', function (e) {
                e.preventDefault();
                jQuery( ".info-text" ).html(jQuery(this).find('.inner-text-template').html());
                jQuery( ".info-text" ).dialog();
            });

            var currentYear = <?php echo date('Y');?>;
            var currentMonth = <?php echo date('m');?>;
            function getMonth(year, month) {
                var data = {
                    action: 'get_month',
                    year: year,
                    month: month,
                    pagesDatesData: {<?php
                        $pagesDatesJs = [];
                        foreach($pagesDates as $key=>$pagesDaysInfo){
                            $pagesDays = [];
                            foreach ($pagesDaysInfo as $day=>$info){
                                $pagesDays[] = "'$day': '$info'";
                            }
                            $pagesDatesJs[] = "'$key': { " . implode(',', $pagesDays) . " }";
                        }
                        echo implode(',', $pagesDatesJs);
                        ?>},
                };

                var url =  '<?php echo admin_url('admin-ajax.php'); ?>';
                jQuery.post( url, data, function(response) {
                    $('.pages-calendar').html(response);
                });
            };
            getMonth(currentYear, currentMonth);

            jQuery(document).on('click', '.previous-month', function (e) {
                e.preventDefault();
                currentMonth--;
                if(currentMonth < 1){
                    currentMonth = 12;
                    currentYear--;
                }
                getMonth(currentYear, currentMonth);
            });

            jQuery(document).on('click', '.next-month', function (e) {
                e.preventDefault();
                currentMonth++;
                if(currentMonth > 12){
                    currentMonth = 1;
                    currentYear++;
                }
                getMonth(currentYear, currentMonth);
            });
        });
    </script>
    <?php
}
