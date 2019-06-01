<?php

include_once('dbConn.php');
include_once('dbTranslations.php');
include_once('config.php');

function get_photos_array_by_post(int $post_id) : array {
	$db = new dbConn();
	$dbt = db_format_translations();

	$query = "
		SELECT photos.id, photos.path, COALESCE(photos.description, '') AS description, COUNT(comments.id) AS nr_comments
		FROM photos
		LEFT JOIN comments ON comments.photo_id = photos.id
		WHERE photos.post_id = ?
		GROUP BY photos.id
		ORDER BY " . $dbt['photos_sort'];

	$res = $db->query($query, $post_id);

	return $res;
}


function get_photo(int $photo_id){
	$db = new dbConn();

	$res = $db->query("
		SELECT photos.id, photos.path, COALESCE(photos.description, '') AS description, posts.title AS post_title, post_id
		FROM photos INNER JOIN posts ON posts.id = photos.post_id WHERE photos.id = ?", $photo_id
	);

	if(count($res) == 0){
		return -1;
	}else{
		return $res[0];
	}
}

function get_newest_photo_id() : int {
	$db = new dbConn();
	$dbt = db_format_translations();

	$query = "
		SELECT photos.id FROM photos
		INNER JOIN posts ON posts.id = photos.post_id
		ORDER BY posts." . $dbt['created'] . " DESC, posts.id DESC, " . $dbt['photos_sort'] . " LIMIT 1";

	$res = $db->query($query);

	if(count($res) == 0){
		// no photo available
		return -1;
	}

	return $res[0]["id"];
}

function get_post_id_created(int $photo_id){
	$db = new dbConn();
	$dbt = db_format_translations();

	$post_id_created = $db->query("
		SELECT posts.id, posts." . $dbt['created'] . " as created
		FROM photos INNER JOIN posts ON posts.id = photos.post_id
		WHERE photos.id = ?", $photo_id);

	if(count($post_id_created) == 0){
		// invalid photo specified
		return -1;
	}else{
		return $post_id_created[0];
	}
}

function get_previous_photo_id(int $photo_id) : int {
	$db = new dbConn();
	$dbt = db_format_translations();

	$post_id_created = get_post_id_created($photo_id);

	if($post_id_created == -1){
		// invalid photo specified
		return -1;
	}

	$res = null;
	if($config['database_format_version'] == '2017'){
		$res = $db->query("
			SELECT photos.id FROM photos INNER JOIN posts On posts.id = photos.post_id
			WHERE (posts.created, posts.id, photos.id) < (?, ?, ?)
			ORDER BY posts.created DESC, posts.id DESC, photos.id DESC
			LIMIT 1",
			$post_id_created["created"], $post_id_created["id"], $photo_id);
	} else {
		$res = $db->query("
			SELECT photos.id FROM photos INNER JOIN posts On posts.id = photos.post_id
			WHERE (posts.created_at, posts.id, -photos.weight) < (?, ?, (
				SELECT weight FROM photos WHERE id = ?
			))
			ORDER BY posts.created_at DESC, posts.id DESC, photos.weight ASC
			LIMIT 1",
			$post_id_created["created"], $post_id_created["id"], $photo_id);
	}

	if(count($res) == 0){
		// no previous photo available
		return -1;
	}else{
		return $res[0]["id"];
	}
}

function get_next_photo_id(int $photo_id) : int {
	$db = new dbConn();

	$post_id_created = get_post_id_created($photo_id);

	if($post_id_created == -1){
		// invalid photo specified
		return -1;
	}

	$res = null;
	if($config['database_format_version'] == '2017'){
		$res = $db->query("
			SELECT photos.id FROM photos INNER JOIN posts On posts.id = photos.post_id
			WHERE (posts.created, posts.id, photos.id) > (?, ?, ?)
			ORDER BY posts.created ASC, posts.id ASC, photos.id ASC
			LIMIT 1",
			$post_id_created["created"], $post_id_created["id"], $photo_id);
	} else {
		$res = $db->query("
			SELECT photos.id FROM photos INNER JOIN posts On posts.id = photos.post_id
			WHERE (posts.created_at, posts.id, -photos.weight) > (?, ?, (
				SELECT weight FROM photos WHERE id = ?
			))
			ORDER BY posts.created_at ASC, posts.id ASC, photos.weight DESC
			LIMIT 1",
			$post_id_created["created"], $post_id_created["id"], $photo_id);
	}

	if(count($res) == 0){
		// no newer photo available
		return -1;
	}else{
		return $res[0]["id"];
	}
}

function generate_pic_html(array $pic, int $prev_id, int $next_id){
	global $config;
	?>
	<a href="./<?= "#post_" . $pic["post_id"]?>"><h1 class="f4 ma2 uppercase"><?= htmlspecialchars($pic["post_title"]) ?></h1></a>

	<img src="<?= $config['photos_base_path'] . $pic["path"] ?>" class="pic ma0 mb1" alt="<?= htmlspecialchars($pic["description"]) ?>" title="<?= htmlspecialchars($pic["description"]) ?>">

	<br>

	<?php if($prev_id != -1){ // link to previous pic ?>
	<a id="prev-link" href="?id=<?= $prev_id ?>" class="f5 pa2" title="previous photo"> <img class="" src="img/lArrow.png"></a>
	<?php } ?>

	<!-- link to main menu -->
	<a href="./<?= "#post_" . $pic["post_id"] . "_" . $pic["id"] ?>" class="f5 pa2" style="position: relative; bottom: -3px;" title="return to overview"> <img class="" src="img/menu.png"></a>

	<?php if($next_id != -1){ // link to next pic ?>
	<a id="next-link" href="?id=<?= $next_id ?>" class="f5 pa2" title="next photo"> <img class="" src="img/rArrow.png"></a>
	<?php } ?>

	<span style="display: inline-block; width: 30px"></span>
	<a href="#comments" class="f5 pa2" id="bt_comments" title="comments"><?= $pic["nr_comments"] ?>&nbsp;<img class="" src="img/cmt.png"></a>

	<p class="f5 mt3 mb3 narrow center"><?= nl2br(trim(htmlspecialchars($pic["description"]))) ?></p>

	<?php
}

?>