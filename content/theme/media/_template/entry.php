<?php kalat_include("header"); ?>

<?php kalat_include("navibar"); ?>

<div class="containar" style="padding:10px;">

	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<h1><?php echo $page["title"]; ?></h1>
			
			<?php kalat_include("breadcrumb"); ?>
			
			<div class="content">
				<?php echo $page["content"]; ?>
				<?php echo $page["author"]; ?>
			</div>
			
			<?php if(kalat_have_previous_entry()){ ?>前へ：<a href="<?php echo $page["previousEntry"]; ?>"><?php echo $page["previousEntry.title"]; ?></a><?php } ?>
			<?php if(kalat_have_next_entry()){ ?>次へ：<a href="<?php echo $page["nextEntry"]; ?>"><?php echo $page["nextEntry.title"]; ?></a><?php } ?>
		</div>
	</div>

</div><!-- /.container -->


<?php kalat_include("footer"); ?>