<?php

function removeContent($report, $db): bool
{
    try {
        $object = $db->table($report->type."s")->get($report->target);
        $object->delete();
    } catch (Exception $e) {
        return false;
    }

    return true;
}

return (function($args) {
    register_experiment("Admin.ModCP");
    assert_user($args);
    assert_su($args);
    $db      = open_database();
    $reports = $db->table("content_reports");

    $report  = $reports->get($args["id"]);
    if(!$report) err();

    if(!is_null($report->verdict)) {
        if($_POST["verdict"] != -1) err(false, 0, "Bad Request", "Состояние объекта изменилось");

        $reports->where("id", $report->id)->update([
            "verdict" => NULL,
            "reopen_count"  => $report->reopen_count + 1,
        ]);
    } else {
        $reports->where("id", $report->id)->update([
            "verdict"       => (int) $_POST["verdict"],
            "verdict_owner" => $args["__user__"]->id,
        ]);
        if($_POST["verdict"] == 1) removeContent($report, $db);
    }

    mkcomment($args["__user__"]->id, $report->id, "content_report", $_POST["reason"]);

    header("HTTP/1.1 302 Redirect");
    header("Location: $_SERVER[REQUEST_URI]");
    exit;
}); 
