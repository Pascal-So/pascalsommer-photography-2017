<?php
	include_once('app/photo.php');
	include_once('app/comment.php');
	include_once('app/config.php');

	$commenting_active = $config['database_format_version'] == '2017';

	$id = -1;
	if(isset($_GET["id"])){
		$id = $_GET["id"];
	}else{
		$id = get_newest_photo_id();
	}

	$prev_id = get_previous_photo_id($id);
	$next_id = get_next_photo_id($id);

	$pic = get_photo($id);
	// check if photo with this id exists
	if($pic == -1){
		die("Photo not available");
		// maybe add a redirect later on to a nicer error page.
	}

	$pic["nr_comments"] = get_nr_comments_by_photo($id);

	$comments = get_comments_array_by_photo($id);
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	<title>Pascal Sommer Photography</title>
	<meta name="description" content="My personal Blog about Photography">
	<meta name="keywords" content="Pascal Sommer,Photography,Photos">
	<meta name="author" content="Pascal Sommer">


	<link rel="stylesheet" type="text/css" href="base.css">
	<link rel="stylesheet" type="text/css" href="view.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

	<script type="text/javascript" src="main.js"></script>
	<script type="text/javascript" src="comments.js"></script>
	<script type="text/javascript" src="jquery.touchwipe.js"></script>

	<script type="text/javascript">
		$(function(){
			scrollToLink($("#bt_comments"), $("#comments"));

			// navigate to next / previous picture without affecting history
			function goto_prev(){
				var prev_link_elem = $("#prev-link");
				if(prev_link_elem.length == 0){
					return;
				}
				var prev_link = prev_link_elem.attr("href");
				window.location.replace(prev_link);
			}
			function goto_next(){
				var next_link_elem = $("#next-link");
				if(next_link_elem.length == 0){
					return;
				}
				var next_link = next_link_elem.attr("href");
				window.location.replace(next_link);
			}

			$(".pic").touchwipe({
				wipeRight: function() {goto_prev();},
				wipeLeft: function() {goto_next();}
			});

			$("#prev-link").click(function(e){
				e.preventDefault();
				goto_prev();
			});

			$("#next-link").click(function(e){
				e.preventDefault();
				goto_next();
			});

			// handle navigation by arrow keys
			$(document).keydown(function(e) {
				if( $("*:focus").is("textarea, input") ){
					// don't detect arrow keys when typing input
					return;
				}

			    switch(e.which) {
			        case 37: // left
			        	goto_prev();
			        	break;
			        case 39: // right
			        	goto_next();
			        	break;
			        default: return; // exit this handler for other keys
			    }
			    e.preventDefault();
			});
		});
	</script>
</head>
<body class="alignCenter">

<?php generate_pic_html($pic, $prev_id, $next_id); ?>

<div id="comments" class="comments ma0 mt4">
	<div id="comments_body">
	<?php
	foreach ($comments as $comment) {
		generate_comment_html($comment);
	}
	?>
	</div>
	<div class="card ma1">
		<h3 class="f5 ma0">New Comment</h3>
		<?php if($commenting_active): ?>
		<form action="comments.php?id=<?php echo $id ?>" method="post" onsubmit="return check_and_send_comment()" class="ma0 mt2">
		<?php else: ?>
		<div class="ma0 mt2">
		<?php endif; ?>
			<label for="tx-name" class="f5">Name: </label><br>
			<input type="text" id="tx-name" name="name" class="ma0 mt1 mb1 textinput"><br>
			<br>
			<label for="tx-comment" class="f5">Comment: </label><br>
			<textarea id="tx-comment" name="comment" class="ma0 mt1 mb1 textinput"></textarea><br>
			<br>
			<input id="tx-photo-id" type="hidden" name="photo_id" value="<?php echo $id ?>">
			<img src="img/loading.png" id="comments_loading" style="display: none">
			<input type="submit" class="f5" value="Send"><br>
		<?php if($commenting_active): ?>
		</form>
		<?php else: ?>
		</div>
		<?php endif; ?>
	</div><br>
	<br>
</div>

</body>
</html>