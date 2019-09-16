<?php

foreach($x->channel->item as $entry) {

	$_link = (string)$entry->link;

	if($_this->debug) Log::init($_link);
	else Log::console($_link);

	$dom->loadFromFile($_link);
	$html = $dom->outerHtml;
	
	$article = $dom->find('article', 0);

	$dom->load($article->innerHtml);
	$picture = $dom->find('picture', 0);

	$dom->load($picture->innerHtml);
	$imgs = $dom->find('img', 0);

	$title = html_entity_decode((string)$entry->title);
	//$title = substr($title, 1, 1) == '"' ? substr($title, 1) : $title;
	//$title = substr($title, -1) == '"' ? substr($title, 0, -1) : $title;

	$description = substr(strip_tags((string)$entry->children("content", true)->encoded), 0, 500);

	$image_url = htmlspecialchars_decode($imgs->getAttribute('src'));

	$news_list[] = new Link(array(
		'title' => $title,
		'description' => $description,
		'base_url' => $entry->link,
		'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
		'image_url' => $image_url,
		'origin_url' => $entry->guid,
		'link2' => $entry->link,
		'content' => $html
	));
}