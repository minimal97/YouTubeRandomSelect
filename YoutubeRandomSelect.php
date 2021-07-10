<?php

    // youtube API
    define( 'YOUTUBE_API_KEY', 'XXXXXXXXXXXXXXXXXXXXXXXXX' );
    // User Name
    define( 'YOUTUBE_USER_NAME', 'XXXXXXXXXXXXXXXXXXXXXX' );

    // アップロードした動画リストへのKey
    $UploadKey = GetUploadsKey(constant('YOUTUBE_USER_NAME') , constant('YOUTUBE_API_KEY'));

    $URL = "https://www.googleapis.com/youtube/v3/playlistItems?playlistId=" .
            $UploadKey . "&maxResults=50&key=" . constant('YOUTUBE_API_KEY');

    $URL_page = $URL . "&pageToken=";

    $html_source = file_get_contents($URL);
    $obj = json_decode($html_source);
    $nextPageToken[0] = "";
    $pageCount = 0;
    $videoCount = 0;

    // NextPageがなくなるまでループ
    while(1) {

        if (strcmp($obj->{'nextPageToken'}, "") == "0") {

            $items = $obj->{'items'};
            $videoCount += count($items);
            break;
        }
        else {

            $pageCount++ ;
            $nextPageToken[] = $obj->{'nextPageToken'};
            $videoCount += 50;
        }

        $html_source = file_get_contents($URL_page . $obj->{'nextPageToken'});
        $obj = json_decode($html_source);

        if ($pageCount > 50) {
            // OVER
            break;
        }
    }

    $URL = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=" .
            $UploadKey . "&maxResults=50&key=" . constant('YOUTUBE_API_KEY');

    $URL_page = $URL . "&pageToken=";

    $html_source = file_get_contents($URL_page . $nextPageToken[$pageCount]);

    // ランダムな動画を選択する
    srand();
    $rnd = rand(1, $videoCount);
    $page = $rnd / 50;
    $cur = $rnd % 50;

    $html_source = file_get_contents($URL_page . $nextPageToken[$page]);
    $obj = json_decode($html_source);

    //var_dump($items[$cur]);           // debug
    {
        $items = $obj->{'items'};
        $snippet = $items[$cur]->{'snippet'};
        $VideoTitle = $snippet->{'title'};                          // VideoTitle

        $resourceId = $snippet->{'resourceId'};
        $VideoId = $resourceId->{'videoId'};                        // VideoId
        $VideoURL = "https://www.youtube.com/watch?v=" . $VideoId;
    }


    // ===============================================================
    //
    //
    function GetUploadsKey($username , $APIkey) {
        $URL = "https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forUsername=" .
               $username .
               "&key=" . $APIkey;
        $html_source = file_get_contents($URL);
        $obj = json_decode($html_source);
        $items = $obj->{'items'};
        $contentDetails = $items[0]->{'contentDetails'};
        $relatedPlaylists = $contentDetails->{'relatedPlaylists'};
        $uploads = $relatedPlaylists->{'uploads'};
        return ($uploads);
    }
?>
