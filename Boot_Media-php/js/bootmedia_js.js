offset = 1;
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

	$(document).on("click","#load-more-button",function(){

		var url = $(this).attr('data-href');
		var date_time = $(this).attr('date_time');

		$(this).text("Loading...");
		$("#load-more-button").attr('disabled', '');

		$.get(url + '&offset=' + offset + "&date_time=" + date_time, function(data) {
		    $(".panel-body").append(data);
		    $("#load-more").remove();
		    if(data !== ''){
		    $(".panel-body").append('<list id="load-more" class="list-group-item"><button id="load-more-button" data-href="' + url + '" date_time="' + date_time + '" class="btn btn-primary">View More</button></list>');
		    }
		});

		new_offset = offset + 1;
		offset = new_offset;

	});

});