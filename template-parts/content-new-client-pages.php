<div class="client-pages">
<?php
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