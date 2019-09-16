<?php

foreach($x->channel->item as $entry) {

	$_link = (string)$entry->link;

	if($_this->debug) Log::init($_link);
	else Log::console($_link);

	$context = stream_context_create([
		"http" => [
			"header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
		]
	]);

	$html = file_get_contents($_link, false, $context);

	$news_list[] = new Link(array(
		'title' => $entry->title,
		'description' => strip_tags((string)$entry->description),
		'base_url' => $entry->link,
		'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
		'image_url' => (string)$entry->enclosure['url'],
		'origin_url' => $entry->guid,
		'link2' => $entry->link
		,'content' => $html
	));
}