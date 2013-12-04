$(function() {
	$( ".nextButton" ).on( "click", function() {
		$(".nextButton").hide();
		$("#laden").show();
		laden();
	});
	
	$( ".nextButton" ).on( "touchend", function() {
		$(".nextButton").hide();
		$("#laden").show();
		laden();
	});
	
	$( ".nextButton" ).on( "touchmove", function() {
		$(".nextButton").hide();
		$("#laden").show();
		laden();
	});
});



function laden(){
	$('#laden h3')
	.delay(250)
	.queue(function() {
		laadwaarde = $(this).html();
		$(this).append(".").dequeue();
	})
	.delay(250)
	.queue(function() {
		$(this).append(".").dequeue();
	})
	.delay(250)
	.queue(function() {
		$(this).append(".").dequeue();
	})
	.delay(500)
	.queue(function() {
		$(this).html(laadwaarde).dequeue();
		laden();
	});
}