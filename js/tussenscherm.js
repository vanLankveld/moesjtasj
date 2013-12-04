$(function() {
	$( ".nextButton" ).on( "click", function() {
		vraagDetails();
	});
	
	$( ".nextButton" ).on( "touchend", function() {
		vraagDetails();
	});
	
	$( ".nextButton" ).on( "touchmove", function() {
		vraagDetails();
	});
});

function vraagDetails(){
	$(".uitleg").hide();
	$(".nextButton").hide();
	$("#laden").show();
	$("#vraagnummer").show();
	$("#categorie").show();
	$("#categorie").html(vak);
	$("#vraagnummer h1 span").html(currentQ);
	laden();	
}

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