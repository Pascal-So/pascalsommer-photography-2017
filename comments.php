<?php

include_once("app/comment.php");
include_once("app/config.php");

if($config['database_format_version'] != '2017') {
    die("Commenting disabled.");
}

if(isset($_POST["photo_id"]) && isset($_POST["name"]) && isset($_POST["comment"])){

	post_comment(intval($_POST["photo_id"]), $_POST["name"], $_POST["comment"]);
}

if(isset($_GET["photo_id"])){
	$photo_id = intval($_GET["photo_id"]);
	$comments = get_comments_array_by_photo($photo_id);

	array_map("generate_comment_html", $comments);
}

?>