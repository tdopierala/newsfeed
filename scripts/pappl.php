<?php

foreach($x->channel->item as $entry) {

    $_link = (string)$entry->link;

    if($_this->debug) Log::init($_link);

    $dom->loadFromFile($_link);
    $img = $dom->find('.art-img',0);

    $image_url = $img->getAttribute('src');

    if(strtotime($entry->pubDate)>time()){
        $date = strtotime('-1 day', strtotime($entry->pubDate));
    } else {
        $date = strtotime($entry->pubDate);
    }

    //var_dump($image_url);

    $news_list[] = new Link(array(
        'title' => (string)$entry->title,
        'description' => trim(strip_tags($entry->description)),
        'base_url' => $entry->link,
        'date' => date("Y-m-d H:i:s",$date),
        'image_url' => $image_url,
        'origin_url' => $entry->guid,
        'link2' => $entry->link
        ,'content' => $dom->outerHtml
    ));
}