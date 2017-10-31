<div class="client-pages">
<?php
$clientPages = getClientPages();
uasort($clientPages, function($firstElement, $secondElement){
    if(!isset($firstElement->startDate)){
        return 1;
    }
    if(!isset($secondElement->startDate)){
        return -1;
    }
    return $firstElement->startDate > $secondElement->startDate ? -1 : 1;
});

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