<?php

return (function($args) {
    assert_user($args);
    assert_security($args, true, false);

    $database = open_database();
    $posts    = $database->table("posts");
    $post     = $posts->get($args["id"]);
    if(!$post) err();

    $liked_by = json_decode($post->liked_by);
    if(in_array($args["__user__"]->id, $liked_by)) {
        $liked_by = array_diff($liked_by, [$args["__user__"]->id]);
        $disliked = true;
    } else {
        array_unshift($liked_by, $args["__user__"]->id);
    }

    $liked_by = json_encode(array_unique($liked_by));
    $posts->where("id", $args["id"])->update(["liked_by" => $liked_by]);

    if($post->owner !== $args["__user__"]->id && !isset($disliked)) {
        $em = EventManager::getInstance();
        $em->triggerEvent((object) [
            "title" => "Ваш пост лайкнули",
            "html"  => "Кому-то понравилась ваша <a href='?/wall&act=view&id=".$post->id."'>запись</a> от ".$post->date,
        ], $post->owner);
    }

    header("HTTP/1.1 302 Found");
    header("Location: $_SERVER[HTTP_REFERER]#_post".$post->id);
    exit;
});
