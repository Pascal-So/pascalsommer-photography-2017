<?php

include_once('dbConn.php');
include_once('dbTranslations.php');

function get_comments_array_by_photo($photo_id){
	$db = new dbConn();
	$created = db_format_translations()['comments_created'];

	$res = $db->query("
		SELECT id, name, comment, DATE_FORMAT({$created}, '%d.%m.%Y, %H:%i') AS created
		FROM comments WHERE photo_id = ? ORDER BY {$created} DESC", $photo_id);

	return $res;
}

function get_nr_comments_by_photo($photo_id){
	$db = new dbConn();

	$res = $db->query("SELECT COUNT(*) AS nr_comments FROM comments WHERE photo_id = ?", $photo_id);

	return $res[0]["nr_comments"];
}

function post_comment($photo_id, $name, $comment){
	$db = new dbConn();

	$db->query("INSERT INTO comments (photo_id, name, comment) values (?, ?, ?)", $photo_id, $name, $comment);
}

function get_all_comments(){
	$db = new dbConn();
	$created = db_format_translations()['comments_created'];

	return $db->query("
		SELECT
			posts.title, photos.id, comments.id as comment_id,
			comments.name, comments.comment,
			DATE_FORMAT(comments.{$created}, '%d.%m.%Y, %H:%i') AS created
		FROM comments
		LEFT JOIN photos ON photos.id = comments.photo_id
		LEFT JOIN posts ON posts.id = photos.post_id
		ORDER BY comments.{$created} DESC");
}

function generate_comment_html($comment){
	?>
	<div class="card ma1">
		<h3 class="f5 ma0"><?php echo htmlspecialchars($comment["name"]) ?> - <?php echo htmlspecialchars($comment["created"]) ?></h3>
		<p class="f5 alignLeft ma0 mt2"><?php echo nl2br(trim(htmlspecialchars($comment["comment"]))) ?></p>
	</div><br>
	<?php
}

?>