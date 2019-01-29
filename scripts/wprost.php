<?php

foreach($x->channel->item as $entry) {

    $_link = (string)$entry->link;
    
    if($_this->debug) var_dump($_link);

    if(explode("/", $_link)[3]=='tygodnik') continue;

    $html = file_get_contents($_link);
    $img = (string)$entry->enclosure->attributes()['url'];

    $news_list[] = new Link(array(
        'title' => $entry->title,
        'description' => strip_tags((string)$entry->description),
        'base_url' => explode('?',$_link)[0],
        'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
        'image_url' => (string)$entry->enclosure->attributes()['url'],
        'origin_url' => $entry->guid,
        'link2' => $entry->link
        ,'content' => $html
    ));
}