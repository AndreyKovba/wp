<div class="client-pages">
<?php
foreach ($clientPages as $clientPage) {
    ?>
    <div class="client-page">
        <div class="client-page-title">
            <?php echo $clientPage->post_title; ?>
        </div>
        <div class="client-page-text">
            <?php
            $text = strip_tags($clientPage->post_content);
            echo (strlen($text) < 205) ? $text : mb_substr($text, 0, 200) . '...';
            ?>
        </div>
    </div>
    <?php
}
?>
</div>