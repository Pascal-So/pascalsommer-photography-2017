<?php

include_once("app/dbConn.php");
include_once("app/comment.php");
include_once("app/config.php");

if($config['database_format_version'] != '2017') {
    die("Admin interface disabled.");
}


session_start();

function redirect($address){
	header("Location: ".$address);
	die();
}

$is_logged_in = isset($_SESSION["access_granted"]) && $_SESSION["access_granted"] == 1;

if(!$is_logged_in){
	redirect("login.php");
}

if(isset($_POST["search_posts"])){
	$data = search_posts($_POST["search_posts"]);

	die(json_encode($data));
}

if(isset($_POST["delete_post"])){
	$id = intval($_POST["delete_post"]);

	$db = new dbConn();

	$count = $db->query("SELECT count(*) as count FROM posts WHERE id = ?", $id);

	if($count[0]["count"] == 0){
		// post doesn't exist;
		die("post doesn't exist");
	}

	$folder_path = $db->query("SELECT slug FROM posts WHERE id = ?", $id)[0]["slug"];



	$db->query("DELETE FROM posts WHERE id = ?", $id);

	$pics_assoc = $db->query("SELECT id, path FROM photos WHERE post_id = ?", $id);

	foreach($pics_assoc as $pic_assoc){
		$pic_id = $pic_assoc["id"];
		$pic_path = $pic_assoc["path"];

		unlink($pic_path); // delete pic from filesystem.

		$db->query("DELETE FROM comments WHERE photo_id = ?", $pic_id);
	}



	$db->query("DELETE FROM photos WHERE post_id = ?", $id);

	if(!rmdir($folder_path)){
		die("couldn't delete folder " . $folder_path);
	}

	die("1"); // ok
}

if(isset($_POST["delete_comment"])){
	$id = intval($_POST["delete_comment"]);

	$db = new dbConn();
	$db->query("DELETE FROM comments WHERE id = ?", $id);

	die();
}


$comments = get_all_comments();

?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	<title>Admin</title>

	<link rel="stylesheet" type="text/css" href="base.css"/>
	<link rel="stylesheet" type="text/css" href="upload.css"/>
	<link rel="stylesheet" type="text/css" href="view.css">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

</head>
<body class="alignCenter">

<script type="text/javascript">

function delete_post(post_id) {
	if(confirm("Do you really want to delete post " + post_id.toString() + "?")){
		$.post("", {delete_post: post_id})
			.done(function (data){
				var ok = data == "1";
				if(ok){
					alert("Deleting was successful.");
				}else{
					alert("Error: " + data);
				}
			})
			.fail(function(){
				alert("Networking error while deleting post");
			});
	}else{
		return;
	}
}

$(function(){
	$("#bt-delete-post").click(function(){
		var post_id = $("#tx-post-id").val();
		delete_post(post_id);
	});

	$(".bt-delete-comment").click(function(e){
		var target = $(e.target);

		var author = target.children("h3").html();

		if(!confirm("Do you really want to delete this comment by " + author + "?")){
			return;
		}

		var comment_id = parseInt(target.children(".comment-id").html());

		$.post("", {delete_comment: comment_id})
			.done(function(data){
				alert("Deleted commment. " + data);
				location.reload();
			})
			.fail(function(){
				alert("Netwokring error while deleting comment");
			});
	});
});
</script>

<h1 class="f1 ma0 mt1 mb5">Administration</h1>


<h2 class="f2 ma0 mt2 mb5">Comments</h2>

<?php

foreach($comments as $comment){
	?>
	<br>
	<h3 class="f5 ma0">In <a href="view.php?id=<?php echo $comment["id"]?>#comments"><?php echo $comment["title"]?></a>:</h3>

	<?php generate_comment_html($comment);?>

	<div class="button f5 mt0 mb4 bt-delete-comment"><div class="hidden comment-id"><?php echo $comment["comment_id"] ?></div>Delete</div>

	<?php
}
?>

<br>
<br>
<h2 class="f2 ma0 mt5 mb5">Delete Post</h2>
<br>
<input class="textinput f5" type="number" name="post_id" id="tx-post-id"><br>
<div class="button f5 mt2 mb8" id="bt-delete-post">Delete</div>

<hr>

<a href="./"><div class="button ma0 mt3 f5">Return to blog</div></a>
<br><br>
<br><br>


</body>
</html>