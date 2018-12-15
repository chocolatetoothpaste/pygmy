<?php
define('PYGMY_PATH', '/var/www/psyncro.com/page/static/tech');

if( isset( $_POST['rm'] ) ) {
	unlink( PYGMY_PATH . DIRECTORY_SEPARATOR . $_POST['file'] );
	header("Location: $_SERVER[SCRIPT_NAME]");
	die;
}

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

	header("Location: $_SERVER[SCRIPT_NAME]?file=/" . $name);
	die;
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

function dir_to_array($dir, $slice = true) {
	$result = array();

	$cdir = array_slice(scandir($dir), 2);
	foreach( $cdir as $key => $value )
	{
		if( is_dir( $dir . DIRECTORY_SEPARATOR . $value ) )
			$result[$value] = dir_to_array( $dir . DIRECTORY_SEPARATOR . $value );
		else
			$result[] = $value;
	}

	return $result;
}

function list_dir($dir, $slice = true) {
	$result = array();

	$fileinfos = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir)
	);
	foreach($fileinfos as $pathname => $fileinfo) {
		if (!$fileinfo->isFile()) continue;
		$result[] = str_replace(PYGMY_PATH, '', $pathname);
	}

	return $result;
}

$files = list_dir(PYGMY_PATH);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Portal</title>
	<script src="/assets/js/vendor/tinymce/tinymce.min.js"></script>

	<!--style>
		.cms-article {
			padding: 5px 0;
		}
		/* .cms-article a.button {
			display: none;
			float: right;
		} */
		.cms-article:hover {
			background: #eee;
		}
		/* .cms-article:hover a.button {
			display: initial;
		} */
		*, *:before, *:after {
		  box-sizing: border-box;
		}

		a.button {
			-webkit-appearance: button;
			text-decoration: none;
			color: initial;
			padding: 2px 7px 3px;
			font-size: 11px;
			font-family: system-ui;
		}
	</style-->
</head>

<body>
	<div style="float: left; width: 40%;">
		<div class=""><a class="button" href="<?=$_SERVER['SCRIPT_NAME']?>">New</a></div>
		<?php foreach( $files as $f ) {
			if( is_file(PYGMY_PATH . "/$f") ) {
				$line = fgets(fopen(PYGMY_PATH . "/$f", 'r'));
				if( strpos($line, '<!--') === false )
					$line = '';
				else
					$line = trim( str_replace( ['<!--', '-->'], '', $line ) );

				$line = "$line - $f";
				echo '<div class="cms-article">' . ( strpos($f, '.draft') !== false ? 'Draft - ' : '' )
					. "<a href=\"index.php?file=$f\">$line</a></div>";
			}
		} ?>
	</div>
	<div style="float: left; width: 60%;">
		<form method="post" action="">
			<input type="hidden" name="file" value="<?=$_GET['file']?>">
			<div>
				<div><input style="width: 100%" type="text" name="title" placeholder="Title" value="<?=$title?>"></div><br style="clear:both">
				<div><input required pattern="[0-9a-zA-Z_\-.]+" style="width: 100%" type="text" name="slug" placeholder="slug" value="<?=$slug?>"></div>
				<br style="clear:both">
			</div>
			<div><textarea name="article"><?=$article?></textarea></div>
			<div>
				<button name="draft">Save Draft</button>
				<button name="publish">Publish</button>

				<button style="float: right;" id="rm" name="rm">Delete</button>
			</div>
		</form>
	<div>

	<script>
		var rmfile = document.getElementById('rm');

		rmfile.addEventListener('click', function(e) {
			var conf = confirm("Are you sure you want to delete this file?");

			if( ! conf ) {
				e.preventDefault();
			}
		})
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
			toolbar: 'insert | undo redo |  formatselect | bold italic backcolor | link unlink | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat',
			content_css: [
				'//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
				'//www.tinymce.com/css/codepen.min.css'
			]
		});
	</script>
</body>

</html>
