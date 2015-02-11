<nav class="navbar navbar-inverse">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo _SITE_PUBLIC_PATH_; ?>"><?php echo kalat_site_name(); ?></a>
		</div>
		<div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav navbar-right">
				<?php
					$pages = kalat_load_configure("navigation");
					foreach($pages as $page){
						_navbar_print_tree($page);
					}
				
				?>
			</ul>
		</div>
	</div>
</nav>
