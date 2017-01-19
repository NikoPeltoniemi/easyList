jQuery(document).ready(function($) {
	$('.ukk').find('.ukk-head').on('click', function(event) {
		event.preventDefault();
		$(this).siblings('.ukk-answer').slideToggle();
	});
});