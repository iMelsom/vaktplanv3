  $( function() {
var record;
var vaktID;
var vaktName;
var uid;
var Date;
var vaktFrom;
var Elevated;
var fravTyp;
var REGuid;
var infoMail;
var dateFrom;
var dateTo;
var FravType;
var Description;
var Merknad;
var Merknad2;
var FravID;
var Valid;
var ResetXV;
var ShowValidCheck;
var guid;
var guid1;
var getUrl = location.host;
var useURL = getUrl.replace("#", "");
var aID;


$('.opener').each(function() {
  guid =  $(this).find('#GUID2').val();
  var panel = $(this).parentsUntil('.jumbotron').siblings('.VaktEdit');
//  if (guid == 4) var panel = $(this).parentsUntil('.jumbotron').siblings('.VaktEdit4');
//  if (guid == 6) var panel = $(this).parentsUntil('.jumbotron').siblings('.VaktEdit6');

  $(this).click(function(event) {
	  event.stopPropagation();
//	  alert(find('#vaktID').val());
	  
	vaktID = $(this).find('#vaktID').val();
	vaktName =  $(this).find('#vaktNavn').val();
	vaktDate =  $(this).find('#vaktDato').val();
	vaktFrom =  $(this).find('#vaktEier').val();
	infoMail =  $(this).find('#sendMail').val();
	uid =  $(this).find('#gjortAv').val();

    $('[name=vID]').val(vaktID);
    $('[name=vName]').val(vaktName);
    $('[name=FUID]').val(vaktFrom);
    $('[name=UID]').val(uid);
    $('[name=printHeader]').text("Vakt ");
    $('[name=printHeader]').append(vaktName);
    $('[name=printHeader]').append(" den " ); 
    $('[name=printHeader]').append(vaktDate);
	panel.dialog('open');
  });
}); 
 
$('.regSyk').each(function() {
 var panel = $(this).parentsUntil('.jumbotron').siblings('.RegSykd');

  
  $(this).click(function(event) {
  
	aID = $( "#AnsattID", this ).val();
	if (!aID){ aID = $( "#Ansatt", this ).val()};

	guid = $( "#GUID1", this ).val();
	if (!guid){ guid = $( "#GUID2", this ).val()};
	
	Name = $(this).find('#ForNavn').val();
	uID = $(this).find('#Test2').val();
	dateFrom = $(this).find('#sykFra').val();
	dateTo 	= $(this).find('#sykTil').val();
	Elevated = $(this).find('#Elevated').val();
	if (Elevated == 0) {infoMail =  $(this).find('#sendMail').val();}
	
	Task = $(this).find('#Task').val();
	FravID = $(this).find('#FravID').val();
	BrukerID = $(this).find('#BrukerID').val();
	Grunn = $(this).find('#Grunn').val();
	dateFrom = $(this).find('#FraDato').val();
	dateTo = $(this).find('#TilDato').val();
	Description = $(this).find('#Description').val();
	ShowValidCheck 		= $(this).find('#ShowValidCheckbox').val();

	$( "#start" ).datepicker({ dateFormat: 'yy-mm-dd',  firstDay: 1 });
	$( "#start" ).datepicker('setDate', dateFrom);
	$( "#slutt" ).datepicker({ dateFormat: 'yy-mm-dd',  firstDay: 1 });
	$( "#slutt" ).datepicker('setDate', dateTo);

	
    $('[name="AnsID"]').val(aID);
    $('[name=UID]').val(uID);

    $('[name=FID]').val(FravID);
	$( "#FravType" ).val(Grunn);
	$( "#Merknad3" ).val(Description);	
	if (ShowValidCheck == 1){
		$("#ValidTest").prop('checked', true);
		}


	$('[name=printHeader]').text("Register ");
	if (Elevated == 1){$('[name=printHeader]').append("fravær ");}
	else {$('[name=printHeader]').append("sykemelding ");}
	if (Elevated != 1){$('[name=printHeader]').append("for ");}
    if (Elevated != 1){$('[name=printHeader]').append(Name);}
	
	
	panel.dialog('open');
  });
}); 
 
$(".VaktEdit").dialog({ 
    autoOpen: false,
    modal: true,
    buttons: {
        OK: function() {
		
			if ( $('input[name="mailNotif4"]').is(':checked') ) {
				infoMail = "send";
			} 
			else {
				infoMail = "quiet";
			}
//alert(infoMail);
			var CheckBoxValue = $(':radio:checked', this).val();
			var SelectedValue = $( "select#vaktTo", this ).val();
			var NewVaktValue = $( "select#endreVaktType", this ).val();
//			var ths = this;
			$.post("vaktplan_files/Turnus_WriteDB.php", 
			{
				vID:vaktID, 
				DESCR:CheckBoxValue, 
				TUID:SelectedValue, 
				NEWV:NewVaktValue, 
				FUID:vaktFrom, 
				Date:vaktDate, 
				MAIL:infoMail, 
				UID:uid,
				GUID:guid
				}, 
			function(value){}
			);
			setTimeout(function(){
				location.reload(true);
				},2000);
			$( this ).dialog( "close" );
        },        
        Cancel: function() {
            $( this ).dialog( "close" );
        }
	}
});	

$(".RegSykd").dialog({ 
    autoOpen: false,
    modal: true,
    buttons: {
        OK: function() {

				FravType = $( "select#FravType", this ).val();
				var Merknad3 = $("#Merknad3", this).val();
			 	aID = $( "#AnsID", this ).val();
				if (!aID){ aID = $( "#Ansatt", this ).val()};

//alert (Merknad3);			
//				alert(Merknad);


				if ( $('input[name="sendMail"]').is(':checked') ) {
					infoMail = "User";
				} 
				else {
					infoMail = "quiet";
				}
				
				if ($('#ValidTest').is(':checked'))Valid = 1;
				else Valid = 0;
				if ($('#ResetXVTest').is(':checked'))ResetXV = 1;
				else ResetXV = 0;
//				alert(ResetXV);
				var go = 1;
				
			if (FravType == 4 && FravType == 10) {infoMail = "All";}
			var SelectedFDATE = $( "#start", this ).val();
			var SelectedTDATE = $( "#slutt", this ).val();
//alert(FravType);
			if (SelectedFDATE > SelectedTDATE) alert('Selv om man av og til skulle ønske tiden\r\nkunne gå baklengs av og til, kan desverre ikke\r\nfravær registreres med startdatoen senere enn sluttdatoen');
			if (!FravType || FravType < 1) alert ('Fraværstype må velges');
			
			if (Valid == 1 && ResetXV == 1){
				 alert ('Vakter kan kun resettes dersom ferie fjernes');
				 go = 0;
				 }
			if (Valid == 0 && ResetXV == 1){
				 alert ('Vakter kan kun resettes dersom ferie fjernes');
				 go = 1;
				 }
			
			if (SelectedFDATE <= SelectedTDATE && FravType > 0 && go == 1){
				$.post("vaktplan_files/Turnus_WriteDB_frav.php", 
				{
				 FRVID:FravID, 
				 FRVTYP:FravType, 
				 UID:aID, 
				 FDATE:SelectedFDATE, 
				 TDATE:SelectedTDATE, 
				 RegUID:uID,
				 MAIL:infoMail,
				 VALID:Valid,
				 RESETXV:ResetXV,
				 DESCR:Merknad3,
				 GrpID:guid
				 }, 
				function(value){}
				);
				setTimeout(function(){   
					$tabGlobals = $( ".tabs" ).tabs( 'option', 'active' );
					
					var urlQstring = location.search;
					var CleandedurlQstring = urlQstring.replace("?", "");

					var urlQstrings = CleandedurlQstring.split('&');
					function checkUrlQueries(urlQstrings) {
					    return (urlQstrings.includes("page"));
					}

					var tabNumberExists = urlQstrings.filter(checkUrlQueries);
					if (tabNumberExists != '' ){
						var editTabNR = urlQstrings.indexOf(tabNumberExists);
						urlQstrings[editTabNR] = 'page='+$tabGlobals;
					}
					else urlQstrings.push('');
					
					makeNewString = urlQstrings.join("&");
					var CleanNewString = makeNewString.replace(",", "");
// alert(CleanNewString);

					location.reload(true);
					//location.replace('https://'+useURL+ '/?' +CleanNewString);
					},2000);

				$( this ).dialog( "close" );
			};	
        },        
        Cancel: function() {
            $( this ).dialog( "close" );
        }
	}
});	


} );