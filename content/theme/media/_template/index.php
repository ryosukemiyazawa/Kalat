<?php kalat_include("header"); ?>

<?php kalat_include("navibar"); ?>

<!-- Main Content -->
	<div class="container">
		<div class="row">
			<div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
				
			<?php if($page["current_page"] < 1){ ?>
			<?php if($page["content"]){ ?>
			<h1><?php echo $page["title"]; ?></h1>
			<?php if($page["subtitle"]){ ?><p><?php echo $page["subtitle"]; ?></p><?php } ?>
			
			<div class="content">
				<?php echo $page["content"]; ?>
			</div>
			
			<hr />
			<?php } ?>
			<?php }else{ ?>
			
			<?php kalat_include("breadcrumb"); ?>
			
			<?php } ?>
			
			<?php if(kalat_have_entries()): ?>
			<?php foreach ($page["entries"] as $entry): ?>
			<div class="post-preview">
				<a href="<?php echo $entry["url"]; ?>">
					<h2 class="post-title"><?php echo $entry["title"]; ?></h2>
					<?php if($entry["subtitle"]){ ?><h3 class="post-subtitle"><?php echo $entry["subtitle"]; ?></h3><?php } ?>
				</a>
				<p class="post-meta">Posted by <a href="#">Start Bootstrap</a> on <?php echo date("M j, Y", $entry["date"]); ?>September 24, 2014</p>
			</div>
			<?php endforeach; ?>
			
			<hr />
			
			<?php if($page["previousPage"]){ ?><a href="<?php echo $page["previousPage"]; ?>">前へ</a><?php } ?>
			<?php if($page["nextPage"]){ ?><a href="<?php echo $page["nextPage"]; ?>">次へ</a><?php } ?>
			
			<?php endif; ?>
		</div>
	</div>

</div><!-- /.container -->


<?php kalat_include("footer"); ?>