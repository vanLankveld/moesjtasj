//=================================== Startscherm vor beginnen spel

function showConfirmScreen() {
    $(".startBevestigSherm").attr('display', 'block');
    $(".startBevestigSherm").show();
}

function hideConfirmScreen() {
    $(".startBevestigSherm").attr('display', 'none');
    $(".startBevestigSherm").hide();
}


//=================================== speler invoerveld functies

function showPlayers() {
    $("#players").attr('display', 'block');
    $("#players").show();
}

function hidePlayers() {
    $("#players").attr('display', 'none');
    $("#players").hide();
    $(".lobby").hide();
}


//=================================== container functies

function showContainer() {
    $("#container").attr('display', 'block');
    $("#container").show();
}

function hideContainer() {
    $("#container").attr('display', 'none');
    $("#container").hide();
}


//=================================== Multiple functies

function showMultiple() {
    $("input:radio[name='antwoordMult']").each(function(i) {
        this.checked = false;
    });
    $(".multipleValue").text("");
    $("#multiple").attr('display', 'block');
    $("#multiple").show();
    $("#antwoord0Value").append(antwoord1);
    $("#antwoord1Value").append(antwoord2);
    $("#antwoord2Value").append(antwoord3);
    $("#antwoord3Value").append(antwoord4);
    $(".radio").removeAttr('disabled');
}

function disableMultiple() {
    $(".radio").attr('disabled', 'disabled');
}

function hideMultiple() {
    $(".multipleLabel").attr('display', 'none');
    $("#multiple").hide();
}


//=================================== enkele vraag functies

function showEnkel() {

    $("#antwoord").removeAttr('disabled');
    $("#antwoord").attr('display', 'block');
    $("#antwoord").show();
}

function disableEnkel() {
    $("#antwoord").attr('disabled', 'disabled');
}

function hideEnkel() {
    $("#antwoord").attr('display', 'none');
    $("#antwoord").hide();
}


//=================================== clearQuestion

function clearQuestion() {
    $("#vraag").html('');
    $("#antwoord").val('');
}


//=================================== tussenscherm showen

function showNext() {
    nextButtonPressed = false;
    $(".overlay").fadeIn(300);
}

function emptyRightAnswer() {
    $("#uitleg").empty();
    wachtOpVerder();
}

function showRightAnswer() {
    console.log("juiste antwoord weergeven");
    $("#uitleg").html("Het juiste antwoord is:<br/>" + correctAnswer);
    uitlegWeergeven();
}
