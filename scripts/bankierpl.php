<?php

foreach($x->channel->item as $entry) {

	$_link = (string)$entry->link;

	if($_this->debug) Log::init($_link);
	else Log::console($_link);

	$dom->loadFromFile($_link);
	$html = $dom->outerHtml;

	$div = $dom->find('#article');

	$dom->load($div);
	$imgs = $dom->find('img');

	$image_url="";
	$_img=null;
	if(count($imgs)>0){

		foreach($imgs as $img){
			
			$_img = $img;
			if($img->getAttribute('class') == 'photo') {
				continue;
			}

			$image_url = $img->getAttribute('src');
			break;
		}

	}

	$news_list[] = new Link(array(
		'title' => $entry->title,
		'description' => strip_tags((string)$entry->description),
		'base_url' => $_link,
		'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
		'image_url' => $image_url,
		'origin_url' => $entry->guid,
		'link2' => $entry->link,
		'content' => $html
	));
}