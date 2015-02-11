<?php kalat_include("header"); ?>

<?php kalat_include("navibar"); ?>

<div class="containar" style="padding:10px;">
	
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<?php if($page["content"]){ ?>
			<h1><?php echo $page["title"]; ?></h1>
			
			<div class="content">
				<?php echo $page["content"]; ?>
			</div>
			
			<hr />
			<?php }else{ ?>
				<h1><?php echo $page["title"]; ?></h1>
			<?php } ?>
			
			<?php kalat_include("breadcrumb"); ?>
			
			<?php if(kalat_have_entries()): ?>
			<?php foreach ($page["entries"] as $entry): ?>
				<a href="<?php echo $entry["url"]; ?>">
				<h3><?php echo $entry["title"]; ?></h3>
				</a>
				<?php echo date("Y-m-d", $entry["date"]); ?>
				<div><?php echo $entry["content"]; ?></div>
				<hr />
			<?php endforeach; ?>
			
			<?php if($page["previousPage"]){ ?><a href="<?php echo $page["previousPage"]; ?>">前へ</a><?php } ?>
			<?php if($page["nextPage"]){ ?><a href="<?php echo $page["nextPage"]; ?>">次へ</a><?php } ?>
			
			<?php endif; ?>
		</div>
	</div>

</div><!-- /.container -->


<?php kalat_include("footer"); ?>