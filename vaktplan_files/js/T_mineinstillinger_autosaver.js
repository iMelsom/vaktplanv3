/**
 * 
 */
// Create the TIMERS object
TIMERS = {};

//
// Save the information from the form by sending it to the server via an
// AJAX GET request. If you have a lot of data you could use POST here
// instead
//
function saveEmailData()
{
    // Get the form information and put it in the opt variable
    var opt = {
            user: document.getElementById('instUID').value,
            altEmail: document.getElementById('instEmail').value,
            sendToBoth: 0,
    };
	if(document.getElementById('instEmailBoth').checked) {
	    opt.sendToBoth = 1;
	}
	//  alert(opt.altEmail);


    // If there's already a timer - cancel it (we're about to create a
    // new one). This means that we don't register many new timers
    if (TIMERS.SAVEDATA) {
        clearTimeout(TIMERS.SAVEDATA);
    }

    // Create the new timer - which runs after a delay of a second
    TIMERS.SAVEDATA = setTimeout(function ()
    {
        var url;
//  	  alert(opt.user);
        if (opt.altEmail == ''){
        	opt.altEmail = 'empty'; 
        }

        // Trigger the AJAX GET request that passes the information
        // to the server (using jQuery)
        $.get(
            url = 'vaktplan_files/T_mineinnstillinger_saver.php?'
                + '&altEmail='     + encodeURIComponent(opt.altEmail)
                + '&UID='     + encodeURIComponent(opt.user)
                + '&SendBoth='     + encodeURIComponent(opt.sendToBoth)
        );

        
        // Add a note to the page that it has been saved
        $('span#savedemail')
            .text('lagret')
            .animate({
                opacity: 1
            });


        // Fade out the note after 5 seconds
        setTimeout(function ()
        {
            $('#savedemail').animate({
                opacity: 0
            }, 250);
        }, 5000);


        // Now, at the end of the timer function, clear any reference
        // that we have of the timer
        TIMERS.SAVEDATA = null;
    }, 1000);
}

function saveStartpagedata()
{
    // Get the form information and put it in the opt variable
    var opt = {
            user: document.getElementById('instUID').value,
            Startpage: document.getElementById('instStartPage').value,
    };
	//  alert(opt.altEmail);

    // If there's already a timer - cancel it (we're about to create a
    // new one). This means that we don't register many new timers
    if (TIMERS.SAVEDATA) {
        clearTimeout(TIMERS.SAVEDATA);
    }

    // Create the new timer - which runs after a delay of a second
    TIMERS.SAVEDATA = setTimeout(function ()
    {
        var url;
//  	  alert(opt.user);
        if (opt.Startpage == ''){
        	opt.Startpage = 'empty'; 
        }

        // Trigger the AJAX GET request that passes the information
        // to the server (using jQuery)
        $.get(
            url = 'vaktplan_files/T_mineinnstillinger_saver.php?'
                + '&Startpage='     + encodeURIComponent(opt.Startpage)
                + '&UID='     + encodeURIComponent(opt.user)
        );

        
        // Add a note to the page that it has been saved
        $('span#savedoppstart')
            .text('lagret')
            .animate({
                opacity: 1
            });


        // Fade out the note after 5 seconds
        setTimeout(function ()
        {
            $('#savedoppstart').animate({
                opacity: 0
            }, 250);
        }, 5000);


        // Now, at the end of the timer function, clear any reference
        // that we have of the timer
        TIMERS.SAVEDATA = null;
    }, 1000);
}


function saveNumMonths()
{
    // Get the form information and put it in the opt variable
    var opt = {
            user: document.getElementById('instUID').value,
            NumMonths: document.getElementById('instNumMonth').value,
    };
	//  alert(opt.altEmail);

    // If there's already a timer - cancel it (we're about to create a
    // new one). This means that we don't register many new timers
    if (TIMERS.SAVEDATA) {
        clearTimeout(TIMERS.SAVEDATA);
    }

    // Create the new timer - which runs after a delay of a second
    TIMERS.SAVEDATA = setTimeout(function ()
    {
        var url;
//  	  alert(opt.user);
        if (opt.NumMonths == ''){
        	opt.NumMonths = 'empty'; 
        }

        // Trigger the AJAX GET request that passes the information
        // to the server (using jQuery)
        $.get(
            url = 'vaktplan_files/T_mineinnstillinger_saver.php?'
                + '&NumMonths='     + encodeURIComponent(opt.NumMonths)
                + '&UID='     + encodeURIComponent(opt.user)
        );

        
        // Add a note to the page that it has been saved
        $('span#savedNumMonths')
            .text('lagret')
            .animate({
                opacity: 1
            });


        // Fade out the note after 5 seconds
        setTimeout(function ()
        {
            $('#savedNumMonths').animate({
                opacity: 0
            }, 250);
        }, 5000);


        // Now, at the end of the timer function, clear any reference
        // that we have of the timer
        TIMERS.SAVEDATA = null;
    }, 1000);
}

function saveDumpUpdate()
{
    // Get the form information and put it in the opt variable
    var opt = {
            user: document.getElementById('instUID').value,
            DumpUpdate: 0,
    };
	if(document.getElementById('instDumpUpdate').checked) {
	    opt.DumpUpdate = 1;
	}
	 //alert(opt.DumpUpdate);

    // If there's already a timer - cancel it (we're about to create a
    // new one). This means that we don't register many new timers
    if (TIMERS.SAVEDATA) {
        clearTimeout(TIMERS.SAVEDATA);
    }

    // Create the new timer - which runs after a delay of a second
    TIMERS.SAVEDATA = setTimeout(function ()
    {
        var url;
//  	  alert(opt.user);
        if (opt.DumpUpdate == ''){
        	opt.DumpUpdate = '0'; 
        }

        // Trigger the AJAX GET request that passes the information
        // to the server (using jQuery)
        $.get(
            url = 'vaktplan_files/T_mineinnstillinger_saver.php?'
                + '&DumpUpdate='     + encodeURIComponent(opt.DumpUpdate)
                + '&UID='     + encodeURIComponent(opt.user)
        );

        
        // Add a note to the page that it has been saved
        $('span#savedDumpUpdate')
            .text('lagret')
            .animate({
                opacity: 1
            });


        // Fade out the note after 5 seconds
        setTimeout(function ()
        {
            $('#savedDumpUpdate').animate({
                opacity: 0
            }, 250);
        }, 5000);


        // Now, at the end of the timer function, clear any reference
        // that we have of the timer
        TIMERS.SAVEDATA = null;
    }, 1000);
}

function savePreDays()
{
    // Get the form information and put it in the opt variable
    var opt = {
            user: document.getElementById('instUID').value,
            PreDays: document.getElementById('instPreDays').value,
    };
	//  alert(opt.altEmail);

    // If there's already a timer - cancel it (we're about to create a
    // new one). This means that we don't register many new timers
    if (TIMERS.SAVEDATA) {
        clearTimeout(TIMERS.SAVEDATA);
    }

    // Create the new timer - which runs after a delay of a second
    TIMERS.SAVEDATA = setTimeout(function ()
    {
        var url;
//  	  alert(opt.user);
        if (opt.PreDays == ''){
        	opt.PreDays = 'empty'; 
        }

        // Trigger the AJAX GET request that passes the information
        // to the server (using jQuery)
        $.get(
            url = 'vaktplan_files/T_mineinnstillinger_saver.php?'
                + '&PreDays='     + encodeURIComponent(opt.PreDays)
                + '&UID='     + encodeURIComponent(opt.user)
        );

        
        // Add a note to the page that it has been saved
        $('span#savedPreDays')
            .text('lagret')
            .animate({
                opacity: 1
            });


        // Fade out the note after 5 seconds
        setTimeout(function ()
        {
            $('#savedPreDays').animate({
                opacity: 0
            }, 250);
        }, 5000);


        // Now, at the end of the timer function, clear any reference
        // that we have of the timer
        TIMERS.SAVEDATA = null;
    }, 1000);
}

function saveColorData(){
    var data = $('form#colorChanger').serialize();
//    alert(data);
    $.post('vaktplan_files/T_mineinnstillinger_saver.php', data, function(response) {
        $('div#inputs').html($(response).find('div#inputs'));
    });

}

function resetColorData(){
    var data = $('form#colorReset').serialize();
//  alert(data);
    $.post('vaktplan_files/T_mineinnstillinger_saver.php', data, function(response) {
        $('div#inputs').html($(response).find('div#inputs'));
    });
}

