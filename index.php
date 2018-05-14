<?php
define('PYGMY_PATH', '/var/www/example/cms');

if( ! empty( $_POST ) ) {
	if( isset( $_POST['draft'] ) ) {
		$name = '.draft.' . $_POST['slug'] . '.html';
	}
	else {
		$name = $_POST['slug'] . '.html';
		if( file_exists( PYGMY_PATH . '/.draft.' . $name ) ) {
			unlink( PYGMY_PATH . '/.draft.' . $name );
		}
	}

	$title = '<!-- ' . $_POST['title'] . ' -->';
	file_put_contents(PYGMY_PATH . "/$name", "$title\n$_POST[article]");

	$_GET['file'] = $name;
}

if( ! empty( $_GET['rm'] ) ) {
	unlink( PYGMY_PATH . DIRECTORY_SEPARATOR . $_GET['rm'] );
}

if( ! empty( $_GET['file'] ) ) {
	$path = PYGMY_PATH . DIRECTORY_SEPARATOR . $_GET['file'];
	$title = fgets(fopen($path, 'r'));
	$article = file_get_contents($path);
	$article = str_replace("$title", '', $article);
	$title = trim( str_replace( ['<!--', '-->'], '', $title ) );
	$slug = str_replace( '.draft.', '', pathinfo($path, PATHINFO_FILENAME) );
}
else {
	$title = '';
	$article = '';
	$slug = '';
}

function dir_to_array($dir) {
	$result = array();

	$cdir = scandir($dir);
	foreach( $cdir as $key => $value )
	{
		if( $value !== '.' && $value !== '..' )
		{
			if( is_dir( $dir . DIRECTORY_SEPARATOR . $value ) )
			{
				$result[$value] = dir_to_array( $dir . DIRECTORY_SEPARATOR . $value );
			}
			else
			{
				$result[] = $value;
			}
		}
	}

	return $result;
}

$files = dir_to_array(PYGMY_PATH);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Portal</title>
	<script src="/assets/js/vendor/tinymce/tinymce.min.js"></script>
</head>
<body>
	<div style="float: left; width: 25%;">
		<div class=""><a class="button" href="<?=$_SERVER['SCRIPT_NAME']?>">New</a></div>
		<?php foreach( $files as $f ) {
			$line = fgets(fopen(PYGMY_PATH . "/$f", 'r'));
			$line = ( strpos($line, '<!--') === false
				? $line = '';
				: $line = trim( str_replace( ['<!--', '-->'], '', $line ) ) );

			echo '<div class="cms-article">' . ( strpos($f, '.draft') !== false ? 'Draft - ' : '' )
				. "<a href=\"index.php?file=$f\">$line</a>"
				. " <a class=\"button\" href=\"?rm=$f\">delete</a></div>";
		} ?>
	</div>
	<div style="float: left; width: 75%;">
		<form method="post" action="">
			<div>
				<div><input style="width: 100%" type="text" name="title" placeholder="Title" value="<?=$title?>"></div><br style="clear:both">
				<div><input style="width: 100%" type="text" name="slug" placeholder="slug" value="<?=$slug?>"></div>
				<br style="clear:both">
			</div>
			<div><textarea name="article"><?=$article?></textarea></div>
			<div>
				<button name="draft">Save Draft</button>
				<button name="publish">Publish</button>
			</div>
		</form>
	<div>

	<script>
		tinymce.init({
			selector: 'textarea',
			autoresize_min_height: 400,
			resize: false,
			menubar: false,
			branding: false,
			plugins: [
				'advlist autolink lists link image charmap print preview anchor textcolor',
				'searchreplace visualblocks code fullscreen',
				'insertdatetime media table contextmenu paste code autoresize'
			],
			toolbar: 'insert | undo redo |  formatselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat',
			content_css: [
				'//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
				'//www.tinymce.com/css/codepen.min.css'
			]
		});
	</script>
</body>

</html>
