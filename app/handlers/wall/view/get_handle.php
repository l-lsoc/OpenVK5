<?php

return (function($args) {
    $database = open_database();
    $posts    = $database->table("posts");
    $post     = $posts->get($args["id"] ?? 1);
    if(!$post) err();

    $comments = $database->table("comments")->where([
        "commentable_type" => "Post",
        "commentable_id"   => $post->id,
    ])->page($args["page"] ?? 1, 10);

    return [
        "post"     => $post,
        "comments" => $comments,
    ];
});
