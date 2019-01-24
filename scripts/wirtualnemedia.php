<?php

foreach($x->channel->item as $entry) {

    $_link = (string)$entry->link;

    $dom->loadFromFile($_link);
    $imgs = $dom->find('.art-imgsa');

    //var_dump($_link);

    if(count($imgs)>0){
        foreach($imgs as $img){

            $image_url = $img->getAttribute('src');
            break;
        }

    } else {

        $art = $dom->find('.article-content');

        $dom->load($art);
        $imgs = $dom->find('img');

        foreach($imgs as $img){

            $image_url = $img->getAttribute('src');
            break;
        }
    }

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
    ));
}