// JavaScript Document
$(document).ready(function () {
	$('#search').keyup(function(event) {
		var search_text = $('#search').val();
		var rg = new RegExp(search_text,'i');
		$('#product_list .product-list .product').each(function(){
 			if($.trim($(this).html()).search(rg) == -1) {
				$(this).parent().css('display', 'none');
 				$(this).css('display', 'none');
				$(this).next().css('display', 'none');
				$(this).next().next().css('display', 'none');
			}	
			else {
				$(this).parent().css('display', '');
				$(this).css('display', '');
				$(this).next().css('display', '');
				$(this).next().next().css('display', '');
			}
		});
	});
});

$('#search_clear').click(function() {
	$('#search').val('');	
	
	$('#product_list .product-list .product').each(function(){
		$(this).parent().css('display', '');
		$(this).css('display', '');
		$(this).next().css('display', '');
		$(this).next().next().css('display', '');
	});
});