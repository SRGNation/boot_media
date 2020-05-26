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

	$(document).on('click','.join-community-button',function(){
		var community = $(this).attr('id');;
		var count = $("#"+community).closest('div').find('.join-community-count').text();

		$("#"+community).attr('disabled', '');

		$.post('/join_community.php', {community:community}, function(data){

			if(data == 'success') {
				$("#join-community").html('<a class="btn btn-primary" href="/communities/'+community+'/unjoin"><span class="join-community-button-text">Unjoin Community</span> <span class="badge"><div class="join-community-count">'+(Number(count) + 1)+'</div></span></a>');
			} else {
				alert('An error occured: '+data);
			}

		});
	});

	$(document).on("click","#load-more-button",function(){

		var url = $(this).attr('data-href');
		var date_time = $(this).attr('date_time');

		$(this).text("Loading...");
		$(this).attr('disabled', '');

		$.get(url + '&offset=' + offset + "&date_time=" + date_time, function(data) {
		    $(".load-more").remove();
		    $(".panel-body").append(data);
		    if(data !== ''){
		    $(".panel-body").append('<li class="list-group-item load-more"><button id="load-more-button" data-href="' + url + '" date_time="' + date_time + '" class="btn btn-primary">View More</button></li>');
		    }
		});

		new_offset = offset + 1;
		offset = new_offset;

	});

});