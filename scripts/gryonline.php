<?php

foreach($x->channel->item as $entry) {

    $_link = (string)$entry->link;

    $html = iconv("Windows-1250","UTF-8",file_get_contents($_link));
    $html = str_replace("'_blank'", "\"_blank\"", $html);

    $dom->load($html);

    $div = $dom->find('.bpic');

    //var_dump($_link);
    
    if(count($div)>0){

        $dom->load($div->innerHtml);

        $c = $dom->find('img');

        $image_url = $c->getAttribute('src');

        if(substr($image_url,0,1) == ".") $sub=2;
        else $sub = 1;

        $url = explode('/',$_link);

        $image_url = $url[0] . "//" . $url[2] ."/". substr($image_url, $sub);

    } else {

        $image_url='';

        $dom->load($html);
        $div = $dom->find('div');
        foreach($div as $d){
            $video = $d->getAttribute('data-embed-video-id');
            if(!is_null($video)){
                $image_url = "https://i.ytimg.com/vi/" . substr($video, 3) . "/maxresdefault.jpg";

                break;
            }
        }
    }

    //var_dump($image_url);

    $news_list[] = new Link(array(
        'title' => $entry->title,
        'description' => strip_tags((string)$entry->description),
        'base_url' => $entry->link,
        'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
        'image_url' => $image_url,
        'origin_url' => $entry->guid,
        'link2' => $entry->link
    ));
}