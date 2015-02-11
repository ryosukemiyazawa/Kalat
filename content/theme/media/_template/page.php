<?php kalat_include("header"); ?>

<?php kalat_include("navibar"); ?>

<div class="containar" style="padding:10px;">

	<div class="row">
		<?php if($page["content"]){ ?>
		<div class="col-md-8 col-md-offset-2">
			<h1><?php echo $page["title"]; ?></h1>
			
			<?php kalat_include("breadcrumb"); ?>
			
			<div class="content">
				<?php echo $page["content"]; ?>
			</div>
			
		</div>
		<?php } ?>
	</div>

</div><!-- /.container -->


<?php kalat_include("footer"); ?>