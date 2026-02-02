  $( function() {
	var endDate;
	var givenTourID;
	var record;
	var date;
	var UserID;
	var GroupID;
	var AssingTID
	
	$('.setEndDateAnsattTour').each(function() {
	  var panel = $(this).parentsUntil('.tViewWrapper').siblings('.setEndDateAnsattTourDialog');
	  
//	  $(this).dblclick(function(event) {
	  $(this).click(function(event) {
		uTendDate	= $(this).find('.usertourEndDate').val();
		var VaktKode = $(this).parent().attr('id');
		var theIdYouWant = $(this).attr('id');

		$( ".usertourEndDate" ).datepicker({ dateFormat: 'yy-mm-dd',  firstDay: 1 });
		$( ".usertourEndDate" ).datepicker('setDate', uTendDate);
		$( "#userTourID" ).val(theIdYouWant);
		$( "#userOffsetcode" ).val(VaktKode);
		//alert (theIdYouWant);

		panel.dialog('open');
	  });
	}); 

	$(".setEndDateAnsattTourDialog").dialog({ 
		width: 450,
		autoOpen: false,
		modal: true,
		buttons: {
			OK: function() {
				var SelectedFDATE = $( ".usertourEndDate", this ).val();
				var UpdateType = "endAnsattTur"; 
				var DataID = $( "#userTourID", this ).val();
				var OffsetCode = $( "#userOffsetcode", this ).val();
				
				 if (OffsetCode.includes("L")) UpdateType = "endLederTur";
				
//				alert (GroupID);

				$.post("vaktplan_files/Turnus_WriteDB_TurnusAdm.php", 
				{ 
					FDATE:SelectedFDATE,
					UPDTP:UpdateType, 
					DATAID:DataID,
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

	$('.setEndDateTour').each(function() {
	  var panel = $(this).parentsUntil('.tViewWrapper').siblings('.setEndDateTourDialog');
	  
	  $(this).click(function(event) {
		uTendDate	= $(this).find('.tourEndDate').val();
		var theIdYouWant = $(this).attr('id');
		var currEndDate = $(this).attr('name');
//		if (uTendDate ==  "") uTendDate =  currEndDate;

//alert (currEndDate);


		$( ".tourEndDate" ).datepicker({ dateFormat: 'yy-mm-dd',  firstDay: 1 });
		$( ".tourEndDate" ).datepicker('setDate', currEndDate);
		$( "#TourID" ).val(theIdYouWant);

		panel.dialog('open');
	  });
	}); 

	$(".setEndDateTourDialog").dialog({ 
		width: 450,
		autoOpen: false,
		modal: true,
		buttons: {
			OK: function() {
				var SelectedFDATE = $( ".tourEndDate", this ).val();
				//var SelectedFDATE = $( ".tourEndDate", this ).val();
//				var DataID = $(this).attr('id');
				var DataID = $( "#TourID", this ).val();
				var endTild = 0;
				if ($('#endTildeling').is(':checked')) endTild = 1;
				var tempGroup = $( "#TourGroupreset", this ).val();

				//alert (DataID);

				$.post("vaktplan_files/Turnus_WriteDB_TurnusAdm.php", 
				{ 
					FDATE:SelectedFDATE,
					UPDTP:"setEndTur", 
					DATAID:DataID,
					ENDTILD:endTild,
					GID:tempGroup,
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

	$('.setStartDateAnsattTour').each(function() {
	  var panel = $(this).parentsUntil('.tViewWrapper').siblings('.planStartDateAnsattTourDialog');
	  
	  $(this).click(function(event) {
		uTendDate	= $(this).find('.usertourStartDate').val();
		var theIdYouWant = $(this).attr('id');
		var GroupID		= $(this).find('#groupassign').val();
		var availAssignData = $(this).find('#freeTourData').val();
		if (availAssignData != "Ingen ledige turer") {var availAssignArray = JSON.parse(availAssignData);};

		$( ".usertourStartDate" ).datepicker({ dateFormat: 'yy-mm-dd',  firstDay: 1 });
		$( ".usertourStartDate" ).datepicker('setDate', uTendDate);
		$( "#assingUID" ).val(theIdYouWant);
		$( "#TourGroup" ).val(GroupID);

		
//		alert (Object.keys(availAssignArray).length);
		
		if (availAssignData == "Ingen ledige turer") {$('#usertourOffsetID').append('<option value="">' + availAssignData + '</option>');};
		if (availAssignData != "Ingen ledige turer") {
			$('#usertourOffsetID').append('<option value=""> Velg Turnus</option>');
		   for (var index = 1; index <= Object.keys(availAssignArray).length; index++) {
	           $('#usertourOffsetID').append('<option id= "'+ availAssignArray[index].TourID +'" value="' + availAssignArray[index].AvailDate + '"> Tur '+ availAssignArray[index].TourID + ', ledig fra '+ availAssignArray[index].AvailDate + '</option>');
		   };
		   };
		

//		alert (availAssignData);

		panel.dialog('open');
	  });
	}); 

	$(".planStartDateAnsattTourDialog").dialog({ 
		width: 450,
		autoOpen: false,
		modal: true,
		buttons: {
			OK: function() {
				var SelectedFDATE = $( ".usertourStartDate", this ).val();
				var SelectedFDATE = $( ".usertourStartDate ", this ).val();
				var AssingUID = $( "#assingUID", this ).val();
				AssingTID = $( "#assingTourID", this ).val();
				GroupID = $( "#TourGroup", this ).val();
				
//				alert (GroupID);

				$.post("vaktplan_files/Turnus_WriteDB_TurnusAdm.php", 
				{ 
					FDATE:SelectedFDATE,
					UPDTP:"giveAnsattTur", 
					tUID:AssingUID,
					GID:GroupID,
					tOFFSET:AssingTID
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



	$('.OpenNewTour').each(function() {
	  var panel = $(this).parentsUntil('.tViewWrapper').siblings('.OpenNewTourDialog');
	  
	  $(this).click(function(event) {
		updateType 	= $(this).find('#EditType').val();
		record 	= $(this).find('#Input').val();
		date	= $(this).find('.TurStart').val();
		GroupID =  $(this).find('#GrID').val();
		$( ".TurStart" ).datepicker({ dateFormat: 'yy-mm-dd',  firstDay: 1 });
		$( ".TurStart" ).datepicker('setDate', date);
		
		//$( "#PoorSods" ).val(GroupID);

//		alert (GroupID);

		panel.dialog('open');
	  });
	}); 

	$(".OpenNewTourDialog").dialog({ 
		width: 450,
		autoOpen: false,
		modal: true,
		buttons: {
			OK: function() {
				var Data = $( "#Input", this ).val();
				var SelectedFDATE = $( ".TurStart", this ).val();
				UserID  = $(this).find('#Ididit').val();
				//GroupID  = $(this).find('#PoorSods').val();
				
//				alert (GroupID);
				
				var ths = this;
				$.post("vaktplan_files/Turnus_WriteDB_TurnusAdm.php", 
				{ 
					FDATE:SelectedFDATE,
					UPDTP:"NyTur", 
					DATA:Data,
					UID:UserID,
					GID:GroupID
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


	var vCode;
	var vBegin;
	var vLen;
	var vShortInfo;
	var vLoc;
	var vVID;

	var VaktKode;
	var Start;
	var Varighet;
	var vKortInfo;
	var vOppmoteSted;
	
	 
	$('.OpenNewShift').each(function() {
	  var panel = $(this).parentsUntil('.tViewWrapper').siblings('.OpenNewShiftDialog');
	  
	  $(this).click(function(event) {
		vVID 		= $(this).find('#vID').val();
		vCode 		= $(this).find('#vKode').val();
		vBegin 		= $(this).find('#vStart').val();
		vLen 		= $(this).find('#vLen').val();
		vOnPremBegin 		= $(this).find('#vOppmStart').val();
		vOnPremLen 		= $(this).find('#vOppmLen').val();
		vShortInfo 	= $(this).find('#vInfo').val();
		vLoc 		= $(this).find('#selectedOppmoteSted').val();
		UserID		= $(this).find('#uid').val();
		GroupID		= $(this).find('#GroupID').val();
		

		$( "#vKde" ).val(vCode);
		$( "#vStrt" ).val(vBegin);
		$( "#vLengde" ).val(vLen);
		$( "#vOppmStrt" ).val(vOnPremBegin);
		$( "#vOppmLengde" ).val(vOnPremLen);
		$( "#vDesc" ).val(vShortInfo);
		$( "#PoorSods" ).val(GroupID);

		$("input[name=vSted][value=" + vLoc + "]").prop('checked', true);

		panel.dialog('open');
	  });
	}); 

	$(".OpenNewShiftDialog").dialog({ 
		width: 450,
		autoOpen: false,
		modal: true,
		buttons: {
			OK: function() {
				VaktKode 	= $(this).find('#vKde').val();
				Start		= $(this).find('#vStrt').val();
				Varighet	= $(this).find('#vLengde').val();
				OppmStart		= $(this).find('#vOppmStrt').val();
				OppmVarighet	= $(this).find('#vOppmLengde').val();
				vKortInfo	= $(this).find('#vDesc').val();
				vOppmoteSted	= $(':radio:checked', this).val();	
				userID 		= $(this).find('#uid').val();
				if (!GroupID || GroupID == '') GroupID = $(this).find('#PoorSods').val();
//		alert(GroupID);
				
	//alert(userID);
				var ths = this;
				var inputOK = 1; 
				
				if (Varighet == parseInt(Varighet,10)) inpuOK = 1;
				else {inputOK = 0; alert("Varighet må være et tall");}
				
			    re = /^\d{1,2}:\d{2}([ap]m)?$/;
			    ro = /(?:[01]\d|2[0123]):(?:[012345]\d):(?:[012345]\d)/;
					if(Start != '' && (!Start.match(re) && !Start.match(ro))) {
					  alert("Invalid time format: " + Start);
					  inputOK = 0; 
					}
				

				if (inputOK == 1){
				$.post("vaktplan_files/Turnus_WriteDB_TurnusAdm.php", 
				{ 
					VKTKODE:VaktKode, 
					START:Start,
					LENGTH:Varighet,
					OSTART:OppmStart,
					OLENGTH:OppmVarighet,
					SHINFO:vKortInfo,
					JOBLOC:vOppmoteSted,
					UPDTP:"NyVaktType",
					VID:vVID,
					UID:userID,
					GID:GroupID
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

	$('.OpenAddVacAdjust').each(function() {
	  var panel = $(this).parentsUntil('.tViewWrapper').siblings('.VacationTourDialog');
	  
	  $(this).click(function(event) {
		updateType 	= $(this).find('#EditType').val();
		record 	= $(this).find('#Input').val();
		date	= $(this).find('.FerieTurStart').val();

	  
		$( ".FerieTurStart" ).datepicker({ dateFormat: 'yy-mm-dd',  firstDay: 1 });
		$( ".FerieTurStart" ).datepicker('setDate', date);


		panel.dialog('open');
	  });
	}); 

	$(".VacationTourDialog").dialog({ 
		width: 480,
		autoOpen: false,
		modal: true,
		buttons: {
			OK: function() {
				var Data = $( "#fInput", this ).val();
				var SelectedFDATE = $( ".FerieTurStart", this ).val();
				var SelectedUSER = $( "#Operator", this ).val();
				var WhoDunnit = $( "#OopsIdiditagain", this ).val();
				var GroupDoneto = $( "#iWorkHere", this ).val();
				
	//alert(SelectedUSER);
				var ths = this;
				$.post("vaktplan_files/Turnus_WriteDB_TurnusAdm.php", 
				{ 
					FDATE:SelectedFDATE,
					UPDTP:"FerieAvvik", 
					DATA:Data,
					USER:SelectedUSER,
					UID:WhoDunnit,
					GID:GroupDoneto
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


  $( function() {
    $( ".Unassigned" ).sortable({
		placeholder: "emptyTur",
		connectWith: ".target",
	  });
    $( ".Assigned" ).sortable({
		placeholder: "emptyTur",
		connectWith: ".target",
		update: function(event, ui) {
			var changedList = this.id;
			var order = $(this).sortable('toArray');
			var positions = order.join(';');
			

			//alert("Dato: " + changedList);
			$.post("vaktplan_files/Turnus_WriteDB_TurnusAdm.php", 
				{ 
				UPDTP:"TurFordeling", 
				tOFFSET:changedList,
				tUID:positions
				 }, 
				function(value){}
			);
			setTimeout(function(){
				location.reload(true);
				},1000);
		}
	});
  });
});

    function singleSelectChangeValue() {
        //Getting Value
        
        var selObj = document.getElementById("usertourOffsetID");
        var selValue = selObj.options[selObj.selectedIndex].value;
        var TempIDValue = selObj.options[selObj.selectedIndex].id;
        //Setting Value
        document.getElementById("usertourStartDate").value = selValue;
        document.getElementById("assingTourID").value = TempIDValue;
    }
