<?php

return (function($args) {
    assert_user($args);
    assert_security($args);
    $em       = EventManager::getInstance();
    $db       = open_database();
    $messages = $db->table("messages");

    $message = $messages->insert([
        "owner"    => $args["__user__"]->id,
        "to"       => $_POST["to"],
        "type"     => "text/plain",
        "date"     => date(DATE_W3C),
        "edited"   => null,
        "content"  => $_POST["content"],
        "liked_by" => "[]",
        "attachments" => "[]",
        "conversation_id" => null,
    ]);

    if($message->owner !== $message->to) {
        $em->triggerEvent((object) [
            "isMessage" => true,
            "code"      => 100,
            "from"      => $message->owner,
            "text"      => substr($message->content, 0, 30)."...",
            "text_full" => $message->content,
        ], $message->to);
    }

    header("HTTP/1.1 202 Accepted");
    exit($GLOBALS["csrf_token"]);
});
