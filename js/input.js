$(document).bind('touchmove', false); // scroll disable

$(document).ready(function(){
	tekstResize();
	$("#sketch").hide();
		
	// textveld focus functie
    $('.number').bind('focus',function() {
		$("body").scrollLeft(0);
		$(".reken .top .image").hide();
        $(".reken .top").css('height','247px');
		$(".reken .bottom").css('height','125px');
		$(".reken .antwoord").css('height','70px');
		$(".reken .vraag").css('marginTop','40px');
		$(".statusbalk").hide();
    });
	
	// textvel focusout functie
	$('.number').focusout(function() {
        $("body").scrollLeft(0);
		$(".reken .top .image").show();
        $(".reken .top").css('height','512px');
		$(".reken .bottom").css('height','256px');
		$(".reken .antwoord").css('height','155px');
		$(".statusbalk").show();
		tekstResize();
    });
	
	// keyboard verbergen
	var hideKeyboard = function() {
		document.activeElement.blur();
		$("input").blur();
	};
	
	// return knop functie
	$(document).keypress(function(e) {
		if(e.which == 13) {
			$("body").scrollLeft(0);
			$(".reken .top .image").show();
			$(".reken .top").css('height','512px');
			$(".reken .bottom").css('height','256px');
			$(".reken .antwoord").css('height','155px');
			$(".statusbalk").show();
			e.preventDefault();
			document.activeElement.blur();
			tekstResize();
		}
	});
	
	$( ".potlood" ).on( "touchend", function() {
		canvasStart();
	});
	
	$( ".potlood" ).on( "touchmove", function() {
		canvasStart();
	});
	
	$( "#thrash" ).on( "touchend", function() {
		canvasReset();
	});
	
	$( "#thrash" ).on( "touchmove", function() {
		canvasReset();
	});
	
	$( "#arrow_down" ).on( "touchend", function() {
		canvasHide();
	});
	
	$( "#arrow_down" ).on( "touchmove", function() {
		canvasHide();
	});
});

// canvas sketchpad

function canvasStart(){
	$("#sketch").show();
	$("#sketch").animate({top: 188},250);
	$(".vraag").css('marginTop','40px');
	canvasSketch();
	
}

function canvasHide(){
	$("#sketch").animate({top: 768},250);
	tekstResize();
	$("#sketch").hide(500);	
}

// Canvas Reset
function canvasReset(){
	$("#sketchpad").remove();
	$("#sketch").append('<canvas id="sketchpad" width="1024" height="520"></canvas>');
	canvasSketch();
	
}

function canvasSketch(){
	var canvas = document.getElementById('sketchpad');
	var context = canvas.getContext('2d');
	
	// create a drawer which tracks touch movements
	var drawer = {
		isDrawing: false,
		touchstart: function(coors){
			context.beginPath();
			context.moveTo(coors.x, coors.y);
			context.fillStyle = '#fff'; // red
			context.strokeStyle = '#fff'; // red
			context.lineWidth = 4;
			this.isDrawing = true;
		},
		touchmove: function(coors){
			if (this.isDrawing) {
				context.lineTo(coors.x, coors.y);
				context.stroke();
			}
		},
		touchend: function(coors){
			if (this.isDrawing) {
				this.touchmove(coors);
				this.isDrawing = false;
			}
		}
	};
	// create a function to pass touch events and coordinates to drawer
	function draw(event){
		// get the touch coordinates
		var coors = {
			x: event.targetTouches[0].pageX,
			y: (event.targetTouches[0].pageY) - 248
		};
		// pass the coordinates to the appropriate handler
		drawer[event.type](coors);
	}
	
	// attach the touchstart, touchmove, touchend event listeners.
	canvas.addEventListener('touchstart',draw, false);
	canvas.addEventListener('touchmove',draw, false);
	canvas.addEventListener('touchend',draw, false);
	
	// prevent elastic scrolling
	document.body.addEventListener('touchmove',function(event){
		event.preventDefault();
	},false);	// end body.onTouchMove		
}

function tekstResize(){
	var strheight = $('.vraag').height();
	var topheight = $('.top').height();
	topheight = topheight - 90;
	var hasimage = $('.image').find('img').length;
	if(hasimage){
		imgheight = topheight - strheight;	
		$('.image').css('height',imgheight+'px');
	}else{
		if(strheight < 47){
			$('.vraag').css({'marginTop':'200px','text-align':'center'});	
		}
	}
}