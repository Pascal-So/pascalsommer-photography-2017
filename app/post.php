<?php

include_once("dbConn.php");
include_once('dbTranslations.php');
include_once('config.php');

function search_posts($pattern){
	$db = new dbConn();

	$search_string = "%${pattern}%";

	$res = $db->query("
		SELECT posts.id, posts.title FROM posts
		LEFT JOIN photos ON photos.post_id = posts.id
		WHERE posts.title LIKE ?
			OR photos.description LIKE ?
		", $search_string, $search_string);

	return $res;
}

function get_post_ids_before($nr, $id){
	$db = new dbConn();
	$created = db_format_translations()['created'];

	$res = $db->query("
			SELECT id FROM posts
			WHERE ({$created}, id) < ((SELECT {$created} FROM posts WHERE id = ?), ?)
			ORDER BY {$created} DESC, id DESC LIMIT ?"
		, $id, $id, $nr);

	return $res;
}

function get_posts_ids_until($id){
	$db = new dbConn();
	$created = db_format_translations()['created'];

	$res = $db->query("
			SELECT id FROM posts
			WHERE ({$created}, id) >= ((SELECT {$created} FROM posts WHERE id = ?), ?)
			ORDER BY {$created} DESC, id DESC"
		, $id, $id);

	return $res;
}

function get_post_data($id){
	$db = new dbConn();
	$created = db_format_translations()['created'];

	$res = $db->query("SELECT id, title, DATE_FORMAT({$created}, '%d.%m.%Y') AS created FROM posts WHERE id = ?", $id);

	if(count($res) == 0){
		return -1;
	}

	return $res[0];
}


function get_newest_post_ids(int $nr) : array {
	$db = new dbConn();
	$created = db_format_translations()['created'];

	$res = $db->query("SELECT id FROM posts ORDER BY {$created} DESC, id DESC LIMIT ?", $nr);

	return $res;
}


// $pics is array of $pic data with added
// field "nr_comments" on each pic.
function generate_post_html(array $post, array $pics){
	global $config;
?>
	<article id="<?= "post_" . $post["id"] ?>">
		<h2 class="f2 ma0 mt2 mb1"><?= htmlspecialchars($post["title"]) ?></h2>
		<h3 class="f5 ma0 mb3"><?= htmlspecialchars($post["created"]) ?></h3>

		<?php foreach($pics as $pic){ ?>
			<a href="view.php?id=<?= $pic["id"] ?>">
				<img id="<?= "post_" . $post["id"] . "_" . $pic["id"]?>" src="<?= $config['photos_base_path'] . $pic["path"] ?>" title="<?= htmlspecialchars($pic["description"]) ?>" class="blogPic" alt="<?= htmlspecialchars($pic["description"]) ?>">
				<p class="f5 mb3 mt05 alignRight"><?= $pic["nr_comments"] ?>&nbsp;<img src="img/cmt.png"></p>
			</a>
		<?php } ?>

	</article>
<?php
}


?>