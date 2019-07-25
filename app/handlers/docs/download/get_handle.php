<?php

return (function($args) {
    $database = open_database();
    $docs     = $database->table("docs");

    $doc = $docs->get($args["id"]);
    if(!$doc) err();

    $hash          = $doc->hash;
    $filename      = SOCN_ROOT."private/userblob_storage/".substr($hash, 0, 2)."/$hash";
    $orig_filename = $doc->original_filename;
    header("Content-Disposition: attachment; filename=$orig_filename");
    readfile($filename);
    exit;
});
