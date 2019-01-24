<?php

foreach($x->channel->item as $entry) {

    $_link = (string)$entry->link;

    //var_dump($_link);

    $news_list[] = new Link(array(
        'title' => (string)$entry->title,
        'description' => strip_tags((string)$entry->description),
        'base_url' => $_link,
        'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
        'image_url' => (string)$entry->children("media", true)->thumbnail->attributes()['url'],
        'origin_url' => $entry->guid,
        'link2' => $entry->link
    ));
}