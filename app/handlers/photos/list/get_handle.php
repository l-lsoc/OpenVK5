<?php

function fetchImages(int $id, int $page)
{
   $database = open_database();

   $rels     = $database
                   ->table("photo_relations")
                   ->select("photo AS id")
                   ->where("album", $id)
                   ->page($page, 10);
   foreach($rels as $photo) {
       $photo = $database->table("photos")->get($photo->id);
       if(!is_null($photo)) yield $photo;
   }
}

return (function($args) {
    $database = open_database();
    $album    = $database->table("albums")->get($args["id"]);
    if(!$album) err();

    $owner = $album->ref("users", "owner");
    $parsedown = new Parsedown();
    $parsedown->setSafeMode(true);

    return [
        "owner"  => $owner,
        "album"  => $album,
        "images" => iterator_to_array(fetchImages($album->id, $args["page"] ?? 1)),
        "parsedown" => $parsedown,
    ];
});
