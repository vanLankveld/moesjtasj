function createTouchEvents() {
    $(".overlay").hide();
    
    $(".submitAnswer").on("touchend", function() {
        timerToZero();
    });
    $(".submitAnswer").on("touchmove", function() {
        timerToZero();
    });
    $(".submitAnswer").click(function() {
        timerToZero();
    });
    
    $("#nextButton").on("touchend", function() {
        nextTouch();
    });
    $("#nextButton").on("touchmove", function() {
        nextTouch();
    });
    $("#nextButton").click(function() {
        nextTouch();
    });
    
    $("#button1").on("touchend", function() {
        loginTouch();
    });
    $("#button1").on("touchmove", function() {
        loginTouch();
    });
    $("#button1").click(function() {
        loginTouch();
    });
}