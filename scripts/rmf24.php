<?php

foreach($x->channel->item as $entry) {

    $_link = (string)$entry->link;
    $html = file_get_contents($_link);

    if($_this->debug) Log::init($_link);

    $news_list[] = new Link(array(
        'title' => $entry->title,
        'description' => strip_tags((string)$entry->description),
        'base_url' => $_link,
        'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
        'image_url' => (string)$entry->enclosure->attributes()['url'],
        'origin_url' => $entry->guid,
        'link2' => $entry->link
        ,'content' => $html
    ));
}