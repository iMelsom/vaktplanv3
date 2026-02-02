  $( function() {
var record;
var fravID;
var fravTyp;
var uid;
var tesTuid;
var REGuid;
var guid;
var infoMail;
var infoMailBool;
var dateFrom;
var dateTo;
var ShowValidCheck;
var assignedXVCheck;
var Elevated = 0;
var ResetXV;
var Valid;
var Merknad;
var Description;



$('.OpenEdit a').click(function(event) {
  event.stopPropagation();
});

$('.OpenEdit').each(function() {
  var panel = $(this).parentsUntil('.jumbotron').siblings('.RegFrav');
  
  $(this).click(function(event) {
	
	fravID 		= $(this).find('#FravID').val();
	fravTyp 	= $(this).find('#FravType').val();
	uid 		= $(this).find('#uID').val();
    guid 		= $(this).find('#GUID_frv').val();
	infoMailBool 	= $(this).find('#sendMail').val();
	dateFrom	= $(this).find('#fraDate').val();
	dateTo 		= $(this).find('#toDate').val();
	REGuid 		= $(this).find('#gjortAv').val();
	Description		= $(this).find('#Description').val();
	ShowValidCheck 		= $(this).find('#ShowValidCheckbox').val();
	assignedXVCheck 		= $(this).find('#assignedXVCheckbox').val();
	Elevated 		= $(this).find('#ElevatedAcc').val();

	var active = $( ".selector" ).tabs( "option", "active" );
	
	
//alert (Elevated);
	
	$( "#datoFra" ).datepicker({ dateFormat: 'yy-mm-dd', firstDay: 1 });
	$( "#datoFra" ).datepicker('setDate', dateFrom);
	$( "#datoTil" ).datepicker({ dateFormat: 'yy-mm-dd', firstDay: 1 });
	$( "#datoTil" ).datepicker('setDate', dateTo);

	
	if (infoMailBool == 0){
		infoMail = "";
		 };

	if (infoMailBool == 1){
		infoMail = "User";
		 };

	if (ShowValidCheck == 0){
		 $("#ValidCheckbox").hide();
		 $("#resetXvakt").hide();
		 };
		 
	if (ShowValidCheck == 1){
		$("#ValidCheckbox").show();
		$("#ValidTest").prop('checked', true);
		if  (Elevated == 1){$("#resetXvakt").show();}
		};
		
	$( "#FravSelect" ).val(fravTyp);
	$( "#Merknad" ).val(Description);

	
 	if (assignedXVCheck == 0 || Elevated == 1) panel.dialog('open');
 	if (assignedXVCheck == 0 || Elevated == 0) panel.dialog('open');
	if (assignedXVCheck == 1 && Elevated == 0) alert('Dine vakter i perioden er fordelt.\r\nTa kontakt med turnusansvarlig eller leder for å slette fravær');
  });
}); 
 
 
$(".RegFrav").dialog({ 
    autoOpen: false,
    modal: true,



    buttons: {
        OK: function() {
			var SelectedValue = $( "select#FravSelect", this ).val();
			var SelectedFDATE = $( "#datoFra", this ).val();
			var SelectedTDATE = $( "#datoTil", this ).val();
			var active = $( ".selector" ).tabs( "option", "active" );
			var go = 1;
			var Valid;
			
			if ($('#ValidTest').is(':checked'))Valid = 1;
			else Valid = 0;
			Merknad = $('#Merknad').val();
			if ($('#ResetXVTest').is(':checked'))ResetXV = 1;
			else ResetXV = 0;
			
	//		alert (ResetXV);

			if (SelectedFDATE > SelectedTDATE) alert('Start er etter Slutt');
			if (!SelectedValue || SelectedValue < 1) alert ('Fraværstype må velges');
			if (Valid == 1 && ResetXV == 1){
				 alert ('Vakter kan kun resettes dersom ferie fjernes');
				 go = 0;
				 };
			if (Valid == 0 && ResetXV == 1){
				 go = 1;
				 };

			
			if (SelectedFDATE <= SelectedTDATE && SelectedValue > 0 && go == 1){
				var ths = this;
				$.post("vaktplan_files/Turnus_WriteDB_frav.php", 
				{
					FRVID:fravID, 
					FRVTYP:SelectedValue, 
					UID:uid, 
					FDATE:SelectedFDATE, 
					TDATE:SelectedTDATE, 
					RegUID:REGuid,
					VALID:Valid,
					RESETXV:ResetXV,
					MAIL:infoMail,
					DESCR:Merknad,
					GrpID:guid,
			 	 }, 
				function(value){}
				);
				setTimeout(function(){
					location.reload(true);
					},2000);

				$( this ).dialog( "close" );
			}
        },        
        Cancel: function() {
            $( this ).dialog( "close" );
        }
	}
});	
});