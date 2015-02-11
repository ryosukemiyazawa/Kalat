<!DOCTYPE html>
<html lang="<?php echo \kalat\site\SiteHelper::get("lang","en"); ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">

<title>Kalat</title>

<!-- Bootstrap core CSS -->
<link href="<?php echo TEMPLATEPATH; ?>css/bootstrap.min.css" rel="stylesheet">

<!-- Custom styles for this template -->
<link href="<?php echo TEMPLATEPATH; ?>css/dashboard2.css" rel="stylesheet">

<script src="<?php echo TEMPLATEPATH; ?>js/jquery.min.js"></script>
</head>

<body>

	<div class="container">
		<div class="row">
			<div id="main" class="col-md-8 col-md-offset-2" style="padding-top:32px">
				<?php echo $html; ?>
			</div>
		</div>
		
		<hr />
		
		
	</div>
	<!-- /.container -->

<script src="<?php echo TEMPLATEPATH; ?>js/manage.js"></script>
<script src="<?php echo TEMPLATEPATH; ?>js/bootstrap.js"></script>

</body>
</html>