<?php

$news_list=[];
foreach($x->channel->item as $entry) {

    $news_list[] = new Link(array(
        'title' => $entry->title,
        'description' => strip_tags($entry->description),
        'base_url' => Url::get($entry->guid),
        'date' => $entry->pubDate,
        'image' => $entry->image,
        'origin_url' => $entry->guid,
        'link2' => $entry->link
    ));
}