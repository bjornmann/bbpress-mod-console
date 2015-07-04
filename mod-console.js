var modConsole = modConsole || {};
(function($) {
	$('.contentExpander').on('click',function(){
		$(this).next('.expandContent').toggle();
	});
	function load_posts_ajax(){
		query = modQuery;
		$.post(ajaxurl, {
			action: 'ajax_custom_load_posts',
			nonce: query.nonce,
			query: $.extend({}, query, {paged: query.paged+1})
		}, function(result){
			$('#modConsoleList').append(result);
			query.paged++;
			query.nonce = $(result).filter('#nonce').text();
			$('.contentExpander').on('click',function(){
				$(this).next('.expandContent').toggle();
			});
		})
	}

	$('a.load-more').on({
		click: function(){
			var $this = $(this);
			load_posts_ajax();
		}
	});
	modConsole.setReviewed = function(postid,status){
	 	if(status == 'no'){
		 	status = 'yes';
	 	}
	 	else{
		 	status = 'no';
	 	}
		$.post(ajaxurl, {
			action: 'ajax_set_reviewed',
		    'postId': postid,
		    'status': status
		}, function(result){
			if(status == 'yes'){
				$('.post-'+postid+' .reviewLink').html('<span style="color:green">&#9679; Reviewed </span>');
				$('.post-'+postid+' .reviewLink').attr('onClick', "modConsole.setReviewed("+postid+",'yes')" );
			}
			else{
				$('.post-'+postid+' .reviewLink').html('<span style="color:red">&#9679; Not Reviewed </span>');
				$('.post-'+postid+' .reviewLink').attr('onClick', "modConsole.setReviewed("+postid+",'no')" );
			}
		})
	}
})(jQuery);