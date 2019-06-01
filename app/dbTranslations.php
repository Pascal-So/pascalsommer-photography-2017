<?php

/**
 * DB Translation
 *
 * Select between the 2017 and 2018 version of some small
 * SQL snippets. If the whole query needs to be adjusted
 * then it's probably better to just write it out twice,
 * but if only small parts of a big query change, then
 * these snippets can be useful.
 */

include_once('config.php');

function db_format_translations() : array {
    global $config;
    $is17 = $config['database_format_version'] == '2017';

    return [
        'posts_created' => $is17 ? 'created' : 'date',
        'comments_created' => $is17 ? 'created' : 'created_at',
        'photos_sort' => $is17 ? 'photos.id DESC' : 'photos.weight ASC',
        'photos_sort_rev' => $is17 ? 'photos.id ASC' : 'photos.weight DESC',
    ];
}

?>