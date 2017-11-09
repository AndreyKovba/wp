<div style="display: none" class="calendar-popup">
    <?php include(locate_template('template-parts/content-calendar.php')); ?>
</div>
<?php
if(isset($_SESSION['client_group'])) {
    ?>
    <script>
        jQuery(document).on('click', '.calendar-link', function (e) {
            e.preventDefault();
            jQuery( ".calendar-popup" ).dialog();
        });
    </script>
    <div class="well">
        <a class="calendar-link" href="#">Kalender</a>
    </div>
    <?php
}