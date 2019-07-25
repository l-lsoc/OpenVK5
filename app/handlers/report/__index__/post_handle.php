<?php

return (function($args) {
    assert_user($args);
    assert_security($args);
    $db      = open_database();
    $reports = $db->table("content_reports");

    if(!in_array($_POST["typ"], ["post", "comment", "message", "photo", "video"])) err(false, 0, "Bad Request", "$args[type] doesn't seem to be reportable");

    $report = $reports->insert([
        "id"          => uniqid(),
        "owner"       => $args["__user__"]->id,
        "target"      => $_POST["id"],
        "type"        => $_POST["typ"],
        "explanation" => $_POST["claim"],
        "time"        => date(DATE_W3C),
    ]);

    exit("Спасибо за активную позицию! Вы можете вернуться на прошлую страницу.<br/>ID Вашего обращения: ".$report->id);
});
