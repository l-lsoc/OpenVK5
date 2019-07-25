<?php

function Message(object $msg, string $url): object
{
    $db   = open_database();
    $user = $db->table("users")->get($msg->owner);

    return (object) [
        "owner" => (object) [
            "id"   => $user->id,
            "name" => $user->first_name." ".$user->last_name,
            "link" => "$url/?/user&id=".$user->id,
            "ava"  => "$url/cdn/".getAvUrl($user->id, 0),
        ],
        "content" => $msg->content,
    ];
}

function getMessages(int $user, int $sel, int $page, string $url = "/"): Traversable
{
    $msgQuery = <<<'EOT'
SELECT * FROM messages WHERE (owner=? AND `to`=?) OR (owner=? AND `to`=?) ORDER BY date DESC LIMIT ? OFFSET ?
EOT;

    $db       = open_database(false);
    $messages = $db->query($msgQuery, $user, $sel, $sel, $user, 20, ($page - 1 ) * 20);

    foreach($messages as $message) yield Message($message, $url);
}

return (function($args) use ($msgQuery) {
    assert_user($args);
    $db    = open_database();
    $user  = $args["id"] ?? $args["__user__"]->id;
    $user  = $db->table("users")->get($user);
    if(!$user) err();

    $messages = iterator_to_array(getMessages($args["__user__"]->id, ($args["id"] ?? $args["__user__"]->id), ($args["page"] ?? 1), $args["__url__"]));
    $messages = array_reverse($messages);

    if($args["json"]) {
        exit(json_encode($messages));
    }

    return [
        "messages" => $messages,
        "user"     => $user,
    ];
});
