
function close_side(){
	$("#sub").hide().removeClass("visibled");
}

function open_side(html){
	$("#sub-container").html(html);
	$("#sub").show().addClass("visibled");
}


function open_menu(){
	$('#sidebar').removeClass('sidebar-close').removeClass('sidebar-hide-caption');
}

$(function(){
	
	var showMenuHandler = null;
	$("#sidebar a").on("mouseover", function(){
		
		if(showMenuHandler){
			return;
		}
		
		
		showMenuHandler = setTimeout(function(){
			open_menu();
		}, 500);
		
	}).on("mouseout", function(){
		console.log("mouseout");
		clearTimeout(showMenuHandler);
		showMenuHandler = null;
	});
	
});