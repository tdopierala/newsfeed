<?php

$news_list=[];
foreach($x->channel->item as $entry) {

    $news_list[] = new Link(array(
        'title' => $entry->title,
        'description' => strip_tags($entry->description),
        'base_url' => $entry->link,
        'date' => $entry->pubDate,
        'image' => (string)$entry->enclosure['url'],
        'origin_url' => $entry->guid,
        'link2' => $entry->link
    ));
}