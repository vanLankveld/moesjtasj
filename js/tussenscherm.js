function uitlegWeergeven() {
    console.log('uitleg weergeven');
    hideVraagnummer();
    showUitleg();
    //hideCategorie();
    showNextButton();
    hideLaadText();
}


function wachtOpVerder() {
    console.log('wachten tot speler op verder klikt');
    hideVraagnummer();
    //hideCategorie();
    showNextButton();
    hideLaadText();
}

function wachtenWeergeven() {
    console.log('wachten weergeven');
    hideNextButton();
    hideUitleg();
    showVraagnummer();
    //showCategorie();
    showLaadText();
}


function showUitleg() {
    $("#uitleg").show();
}

function hideUitleg() {
    $("#uitleg").hide();
}

function showVraagnummer() {
    $("#vraagnummer h1 span").html(currentQuestion);
    $("#vraagnummer").show();
}

function hideVraagnummer() {
    $("#vraagnummer").hide();
}

function showCategorie() {
    $("#categorie").show();
    $("#categorie").html(vak);
}

function hideCategorie() {
    $("#categorie").hide();
}

function showNextButton() {
    $(".button-container").show();
}

function hideNextButton() {
    $(".button-container").hide();
}

function showLaadText() {
    $("#laden").show();
    laden();
}

function hideLaadText() {
    $("#laden").hide();
}

function laden() {
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
