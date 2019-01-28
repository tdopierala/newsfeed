<?php

foreach($x->channel->item as $entry) {

    $_link = (string)$entry->link;

    $dom->loadFromFile($_link);
    $div = $dom->find('.post-image');

    if(strlen($div)>0){

        $style = $div->getAttribute('style');
        preg_match('/\(.*?\)/', $style, $out);

        $image_url = substr($out[0],1,strlen($out[0])-2);
    } else {
        continue;
    }

    //var_dump($image_url);

    $news_list[] = new Link(array(
        'title' => $entry->title,
        'description' => strip_tags((string)$entry->description),
        'base_url' => $entry->link,
        'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
        'image_url' => $image_url,
        'origin_url' => $entry->guid,
        'link2' => $entry->link
    ));
}