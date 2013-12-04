$(function() {
	laden();
});
function laden(){
	$('#laden span')
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