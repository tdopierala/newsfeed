<?php

foreach($x->channel->item as $entry) {

	$_link = (string)$entry->link;

	if($_this->debug) Log::init($_link);
	else Log::console($_link);

	$content = file_get_contents($_link);
	$content = preg_replace(['#<script .*?>(.*?)</script>#si','#<script>(.*?)</script>#si'], '', $content);

	$dom->load($content);

	if(strlen($dom->innerHtml)==0) {
		var_dump("Link upload false (".$_link.")");
		continue;
	}

	$imgs = $dom->find('.art-img');
	$art = $dom->find('.article-content');

	if(count($imgs)>0){

		foreach($imgs as $img){
			$image_url = $img->getAttribute('src');
			break;
		}

	} elseif(count($imgs)>0) {

			$dom->load($art);
			$imgs = $dom->find('img');

		foreach($imgs as $img){

			$image_url = $img->getAttribute('src');
			break;
		}

	} else {
		var_dump("Link upload false (3)");
		continue;
	}

	if(strtotime($entry->pubDate)>time()){
		$date = strtotime('-1 day', strtotime($entry->pubDate));
	} else {
		$date = strtotime($entry->pubDate);
	}

	$news_list[] = new Link(array(
		'title' => (string)$entry->title,
		'description' => trim(strip_tags($entry->description)),
		'base_url' => $entry->link,
		'date' => date("Y-m-d H:i:s",$date),
		'image_url' => $image_url,
		'origin_url' => $entry->guid,
		'link2' => $entry->link
		,'content' => $content
	));
}