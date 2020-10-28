$(document).ready(function(){
	$('.openCommentModal').click(function(){
		$('.modal-background').show();
		$('.tweetModal-wrapper').show();
	});

	$('.closeBtn').click(function(){
		$('.modal-background').hide();
		$('.tweetModal-wrapper').hide();
		$('.searchModal-wrapper').hide();
		$('.imgModal-wrapper').hide();
		$('.configModal-wrapper').hide();
	});

	$('.openSearchModal').click(function(){
		$('.modal-background').show();
		$('.searchModal-wrapper').show();
	});

	$('.openImgModal').click(function(){
		$('.modal-background').show();
		$(this).addClass("imgModal-container");
	});

	$('.openConfigModal').click(function(){
		$('.modal-background').show();
		$('.configModal-wrapper').show();
	});

	$('.modal-background').click(function(){
		$('.contentImg').removeClass("imgModal-container");
		$('.modal-background').hide();
		$('.tweetModal-wrapper').hide();
		$('.searchModal-wrapper').hide();
		$('.configModal-wrapper').hide();		
	});

});
