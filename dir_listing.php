<!doctype html>
<html lang="en">
<head>
	<title>Directory Listing</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
</head>
<?php
// Programm for recursive listing of a directory
// If non-absulute path given, then using current working directory

// Sort directory listing: dirs go first (in alphabetical order), files go after dirs (in alphabetical order)
function dir_first_sort($x, $y)
{
	if(is_dir($x) && !is_dir($y))
		return -1;
	if(!is_dir($x) && is_dir($y))
		return 1;
	if(is_dir($x) && is_dir($y))
		return $x > $y ? 1 : -1;
	if(!is_dir($x) && !is_dir($y))
		return $x > $y ? 1 : -1;
}

// php function scandir() returns items without path, so here we make path
function make_full_path(&$value, $key, $dirname)
{
	$value=$dirname.$value;
}


// my_scandir() - recursive scanning of a directory
function my_scandir($dirname, &$total_dirs, &$total_files, $collapse=0)
{
	// count number of dirs
	$total_dirs++;
	// uncollapse first level
	$show='';
	if(!$collapse)		// first level has collapse=0
		$show='show';

	// directory's name must have '/' at the end
	if($dirname[strlen($dirname)-1] != '/')
			$dirname.='/';

	// get list of items in given directory
	if(@($items=scandir($dirname)) === false)
	{
		// get error description
		$scd_error=error_get_last();
		$scd_error=explode(':', $scd_error['message']);
		$scd_error=end($scd_error);
		print "<span class=\"text-danger\">Cannot open $dirname <span class=\"text-uppercase\">$scd_error</span></span>";
		
		return $collapse;
	}
	array_walk($items, 'make_full_path', $dirname);
	usort($items, 'dir_first_sort');
	
	// print the items of given directory
	if($items!==false)
	{
		print "<div id='collapse$collapse' class='panel-collapse collapse $show'>";
			print "<ul>\n";
			foreach($items as $item)
			{
				// skip '.' - itself and '..' - parent
				if((basename($item) == '.') || (basename($item) == '..'))
					continue;
				else if(is_dir($item))	// we have another dir, so go deeper
				{
					$col='#collapse'.++$collapse;
					print "<li><b><a data-toggle='collapse' href='$col'>".basename($item)."</a></b></li>\n";
					$collapse=my_scandir($item, $total_dirs, $total_files, $collapse);
				}
				else 	// we have regular file
				{
					print "<li>".basename($item)."</li>\n";
					$total_files++;		// count all files
				}
			}
			print "</ul>\n";
		print '</div>';
	}	
	return $collapse;
}
?>


<body class="bg-light">
	<div class="container-fluid">
		<p class="text-muted">Your current directory: <?php print '<span class="text-primary">'.getcwd().'</span>'; ?></p>
		<form method="POST" class="form">
			<div class="form-group">
				<label for="dirname" class="text-muted">Enter directory name (may use . and ..):</label>
				<div class="input-group">
					<input type="text" id="dirname" name="dirname" class="form-control col-md-4">
				
					<span class="input-group-btn"><input type="submit" value="Show" class="btn btn-primary"></span>
				</div>
			</div>
		</form>
		<?php
		if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['dirname']))
		{
			// validate input
			$dirname=stripslashes(strip_tags($_POST['dirname']));
			print "<b><a data-toggle='collapse' href='#collapse0'>".$dirname."</a></b>\n";
			$total_dirs=0;
			$total_files=0;
			my_scandir($dirname, $total_dirs, $total_files, 0);
			print "<p class=\"text-warning\">Total number of dirs: $total_dirs</p>";
			print "<p class=\"text-warning\">Total number of files: $total_files</p>";
		}	
		?>
	</div>
</body>
</html>
