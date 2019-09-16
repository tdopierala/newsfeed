<?php

foreach($x->channel->item as $entry) {

	$_link = (string)$entry->link;

	if($_this->debug) Log::init($_link);
	else Log::console($_link);

	$dom->loadFromFile($_link);
	$html = $dom->outerHtml;
	$div = $dom->find('.image_big');

	$dom->load($div->innerHtml);

	switch(explode('/', $_link)[3]){
		case 'news': 
			$c = $dom->find('meta');
			$image_url = $c->getAttribute('content');
		break;
		case 'video':
		case 'recenzje':
		case 'publicystyka':
			$c = $dom->find('img')[1];
			$image_url = $c->getAttribute('src');
		break;
		default:
			$image_url = '';
		break;
	}

	$news_list[] = new Link(array(
		'title' => $entry->title,
		'description' => strip_tags((string)$entry->description),
		'base_url' => $_link,
		'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
		'image_url' => $image_url,
		'origin_url' => $entry->guid,
		'link2' => $entry->link
		,'content' => $html
	));
}