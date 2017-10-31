<div class="pages-calendar">
</div>
<?php
add_action('wp_footer', 'my_action_javascript', 99); // для фронта
function my_action_javascript() {
    ?>
    <script type="text/javascript" >
        jQuery(document).ready(function() {
            var currentYear = <?php echo date('Y');?>;
            var currentMonth = <?php echo date('m');?>;
            function getMonth(year, month) {
                var data = {
                    action: 'get_month',
                    year: year,
                    month: month,
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
