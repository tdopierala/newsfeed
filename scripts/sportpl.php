<?php

foreach($x->channel->item as $entry) {

	$_link = (string)$entry->link;

	if($_this->debug) Log::init($_link);
	else Log::console($_link);

	$html = file_get_contents($_link);

	$news_list[] = new Link(array(
		'title' => $entry->title,
		'description' => strip_tags((string)$entry->description),
		'base_url' => $_link,
		'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
		'image_url' => (string)$entry->enclosure['url'],
		'origin_url' => $entry->guid,
		'link2' => $entry->link
		,'content' => $html
	));
}