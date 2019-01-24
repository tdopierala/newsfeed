<?php

foreach($x->channel->item as $entry) {

    $news_list[] = new Link(array(
        'title' => $entry->title,
        'description' => strip_tags((string)$entry->description),
        'base_url' => (string)$entry->link,
        'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
        'image_url' => (string)$entry->enclosure['url'],
        'origin_url' => $entry->guid,
        'link2' => $entry->link
    ));
}