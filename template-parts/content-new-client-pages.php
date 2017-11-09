<div class="client-pages">
<?php
$clientPages = sortClientPagesByDate(getClientPages(), true);
foreach ($clientPages as $clientPage) {
    ?>
    <div class="client-page">
        <a class="client-page-title" href="<?php echo get_permalink($clientPage->ID); ?>">
            <?php echo $clientPage->post_title; ?>
        </a>
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