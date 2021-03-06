<?php
define('PYGMY_PATH', '/var/www/psyncro.com/page/static/tech');
define('PYGMY_EXTENSION', '.html');

if( isset( $_POST['rm'] ) ) {
	unlink( PYGMY_PATH . DIRECTORY_SEPARATOR . $_POST['file'] );
	header("Location: $_SERVER[SCRIPT_NAME]");
	die;
}

if( ! empty( $_POST ) ) {
	$pathinfo = pathinfo($_POST['path']);
	$dir = trim($pathinfo['dirname'],'/');

	if( strpos($_POST['path'], '/') !== false ) {
		if( ! is_dir(PYGMY_PATH . DIRECTORY_SEPARATOR . $dir) ) {
			mkdir(PYGMY_PATH . DIRECTORY_SEPARATOR . $dir, 0775, true);
		}
	}

	$draft = $dir . DIRECTORY_SEPARATOR . '.draft.' . $pathinfo['filename'];

	$name = ( isset( $_POST['draft'] )
		? $draft
		: $_POST['path'] ) . PYGMY_EXTENSION;

	$draft_path = PYGMY_PATH . DIRECTORY_SEPARATOR . $draft . PYGMY_EXTENSION;

	if( file_exists( $draft_path ) ) {
		unlink( $draft_path );
	}

	$title = '<!-- ' . $_POST['title'] . ' -->';
	file_put_contents(PYGMY_PATH . "/$name", "$title\n$_POST[article]");

	header("Location: $_SERVER[SCRIPT_NAME]?file=" . $name);
	die;
}

if( ! empty( $_GET['file'] ) ) {
	$file = PYGMY_PATH . DIRECTORY_SEPARATOR . $_GET['file'];
	$title = fgets(fopen($file, 'r'));
	$article = file_get_contents($file);
	$article = str_replace("$title", '', $article);
	$title = trim( str_replace( ['<!--', '-->'], '', $title ) );
	$path = str_replace( ['.draft.', PYGMY_EXTENSION], '', $_GET['file'] );
}
else {
	$title = '';
	$article = '';
	$path = '';
}

function list_files($dir, $slice = true) {
	$result = array();

	$fileinfos = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir)
	);

	foreach( $fileinfos as $pathname => $fileinfo ) {
		if ( ! $fileinfo->isFile() )
			continue;

		$result[] = str_replace(PYGMY_PATH, '', $pathname);
	}

	return $result;
}

$files = list_files(PYGMY_PATH);

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
	<!-- <div style="float: left; width: 40%;">
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
	</div> -->
	<div>
		<form method="post" action="">
			<div>
				<select style="width: 100%" name="file" id="file">
					<option>- Select file to edit-</option>
					<?php
						$opts = [];
						foreach( $files as $f ) {
							if( is_file(PYGMY_PATH . "/$f") ) {
								$line = fgets(fopen(PYGMY_PATH . "/$f", 'r'));
								if( strpos($line, '<!--') === false )
									$line = '';
								else
									$line = trim( str_replace( ['<!--', '-->'], '', $line ) );

								$selected = ( $f === $_GET['file'] ? 'selected' : '' );

								$opts[$line] = "<option value=\"$f\" $selected>"
									. ( strpos($f, '.draft') !== false ? 'Draft - ' : '' )
									. "$line - $f</option>";
							}
						}
						ksort($opts);
						echo implode('', $opts);
					?>
				</select>
			</div>
			<br style="clear:both">
			<input type="hidden" name="file" value="<?=$_GET['file']?>">
			<div>
				<div><input style="width: 100%" type="text" name="title" placeholder="Title" value="<?=$title?>"></div>
				<br style="clear:both">
				<div><input required pattern="[0-9a-zA-Z_\-.\/]+" style="width: 100%" type="text" name="path" placeholder="path" value="<?=$path?>"></div>
				<br style="clear:both">
			</div>
			<div>
				<textarea name="article"><?=$article?></textarea>
				<br style="clear:both">
			</div>
			<div>
				<button name="draft">Save Draft</button>
				<button name="publish">Publish</button>

				<button style="float: right;" id="rm" name="rm">Delete</button>
			</div>
		</form>
	<div>

	<script>
		var rmfile = document.getElementById('rm');
		var selfile = document.getElementById('file');

		rmfile.addEventListener('click', function(e) {
			var conf = confirm("Are you sure you want to delete this file?");

			if( ! conf ) {
				e.preventDefault();
			}
		})

		selfile.addEventListener('change', function(e) {
			window.location.href = window.location.pathname + '?file=' + this.value;
		});

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
