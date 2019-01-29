<?php

foreach($x->channel->item as $entry) {

    $_link = (string)$entry->link;
    $html = file_get_contents($_link);

    if($_this->debug) var_dump($_link);

    $news_list[] = new Link(array(
        'title' => $entry->title,
        'description' => strip_tags($entry->description),
        'base_url' => Url::get($entry->guid),
        'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
        'image_url' => $entry->image,
        'origin_url' => $entry->guid,
        'link2' => $entry->link
        ,'content' => $html
    ));
}