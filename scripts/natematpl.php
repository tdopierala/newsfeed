<?php

foreach($x->channel->item as $entry) {

	$_link = (string)$entry->link;

	if($_this->debug) Log::init($_link);
	else Log::console($_link);

	$ch = curl_init($_link);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)");
	curl_setopt($ch, CURLOPT_MAXREDIRS, 2); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$html = curl_exec($ch);
	curl_close($ch);

	$news_list[] = new Link(array(
		'title' => (string)$entry->title,
		'description' => strip_tags((string)$entry->description),
		'base_url' => $_link,
		'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
		'image_url' => (string)$entry->children("media", true)->thumbnail->attributes()['url'],
		'origin_url' => $entry->guid,
		'link2' => $entry->link
		,'content' => $html
	));
}