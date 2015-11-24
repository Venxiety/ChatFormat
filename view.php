<?php
$chatRegex = '/\[(?:(?!\n\[).)*/s';
$linkRegex = '@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@';

if (isset($_GET['id'])) {
	$id = $_GET['id'];
}

echo($id);

$config = parse_ini_file('./config/config.ini');
$connection = mysqli_connect($config['host'], $config['username'], $config['password'], $config['dbname'], $config['port']);

if ($connection->connect_errno) {
	echo "<div class='alert alert-danger'>Could not connect to database.</div>";
}

$tablename = $config['tablename'];

$stmt = $connection->prepare("SELECT log FROM $tablename WHERE strid=?");

$stmt->bind_param('s', $id);
$stmt->execute();

$stmt->store_result();
$stmt->bind_result($returned_log);

$stmt->fetch();

$chat = preg_replace($linkRegex, '<a href="$1" target="_blank">$1</a>', $chat);
?>
<!DOCTYPE html>
<html>
<head>
	<title>ChatFormat</title>

	<link rel="stylesheet" type="text/css" href="https://chatformat.com/style/style.css">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="ChatFormat - Format your chat logs!">
	<meta name="keywords" content="chat,format,skype,logs">
	<style type="text/css">
		#page-wrap {
			padding-bottom: 100px;
		}
	</style>
</head>
<body>
	<?php include_once("assets/analytics.php") ?>
	<div id="page-wrap">
		<h1><a href="https://chatformat.com">ChatFormat</a></h1>
		<div class="links"><a id="copy-button">Copy Link</a></div>

		<div class="chat-section">
			<?php
			if ($returned_log != null && $returned_log != "") {
				$firstauthor = null;
				preg_match_all("/\[(?:(?!\n\[).)*/s", $chat, $matches);
				foreach($matches as $array){
					foreach ($array as $match) {
						preg_match("@\[([^]]*)] ([^]]*): (.*)@s", $match, $s);
						$time = $s[1];
						$author = $s[2];
						$message = $s[3];

						if (!isset($firstauthor)) {
							$firstauthor = $author;
						}

						echo '<div class="info">';
						echo '<span class="author">' . $author . '</span>';
						echo ' - ';
						echo '<span class="time">' . $time . '</span>';
						echo '</div>';
						if ($author == $firstauthor) {
							echo '<div class="message first">' . $message . '</div>';
						}
						else {
							echo '<div class="message">' . $message . '</div>';
						}
					}
				}
			}
			else {
				echo "<h3>That log doesn't exist O.o</h3>";
			}
			?>
		</div>

		<?php include_once("assets/footer.php"); ?>
		<?php 
			if ($returned_log != null && $returned_log != "") {
				echo "<div><a href='https://chatformat.com/takedown'>Request Removal</a></div>";
			}
		?>

	</div>
	<script async type="text/javascript" src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
	<script async type="text/javascript" src="https://chatformat.com/zeroclipboard/dist/ZeroClipboard.min.js"></script>
	<script type="text/javascript">
		var client = new ZeroClipboard($("#copy-button"));
		client.on( "copy", function (event) {
			var clipboard = event.clipboardData;
			clipboard.setData("text/plain", "<?php echo 'https://chatformat.com'.$_SERVER['REQUEST_URI']; ?>");
			$("#copy-button").text("Copied!");
		});
	</script>
</body>
</html>