$(document).ready(function(){

	$(document).on('click','.like-button',function(){
		var post = $(this).attr('id');
		var likeType = $(this).attr('liketype');
		var remove = $(this).attr('remove');

		$("#"+post).attr('disabled', '');

		$.post('/like.php', {post:post, likeType:likeType, remove:remove}, function(data){

			if(data == 'success') {
				if(remove == 0) {
				$('#'+post).find('.like-button-text').text('Unlike');
				$('#'+post).closest('li').find('.like-count').text(Number($('#'+post).closest('li').find('.like-count').text()) + 1);
				$("#"+post).removeAttr('disabled');
				$("#"+post).removeAttr('remove');
				$("#"+post).attr('remove','1');
				} else {
				$('#'+post).find('.like-button-text').text('Like');
				$('#'+post).closest('li').find('.like-count').text(Number($('#'+post).closest('li').find('.like-count').text()) - 1);
				$("#"+post).removeAttr('disabled');
				$("#"+post).removeAttr('remove');
				$("#"+post).attr('remove','0');
				}
			} else {
				alert('An error occured: '+data);
			}

		});
	});

});