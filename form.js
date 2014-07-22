/**
 * Adds some interactiveness to the pill schedule form. Functionalities implemented: 
 * autocomplete, copy from one cell to all, hide/show days when clicked
 * @author Lukas Greblikas
 * @email L.Greblikas@cairnsolutions.com
 */

/**
 * Variable for keeping which element was focused the last.
 * When "copy from one to all" button pressed it copies all the 
 * values from the cell which was last focused.
 */
var lastFocus = null;

/**
 * This is called when red X button next to each pill name is pressed.
 * All it does it just removes the hidden input element for that pill name,
 * removes the pill name button itself and removes X button associated with that pill name.
 * @param {element} caller X button which was pressed as an element
 */
function removePillName(caller){
	lastFocus = $(caller).parents("td"); //look issue #13
	$('input[value="'+$(caller).attr("data-id")+'"]').remove();
	$(caller).parent().parent().remove();
}

/**
 * Called when document finished loading
 */
$(document).ready(function(){
	/**
	 * Pill names suggestions array for autocomplete
	 */
	var possibleNames = [];  
	
	var pillField = $(".pillNameInput");
	
	/**
	 * AJAX call to get apikey from emonCMS
	 */
	$.getJSON("/emoncms/user/get.json", "", function(userData){
	
		/** 
		 * AJAX call to fill possibleNames array with
		 * pill names allready existing in database
		 */
		$.getJSON("/emoncms/pills/pillNames.json", userData["apikey_read"], function(data){ 
			for(var i = 0; i < data.length; i++)
				possibleNames.push(data[i]);
		});
	});
	
	/**
	 * A helper function which makes the new entered pill name to appear in the cell.
	 * Constructs the button and hidden input elements with correct values. Also adds
	 * them to document.
	 * @param {element} caller input text field element where the new pill name was entered
	 */
	function pillNameEntered(caller){
		if(isNewName(caller)) {
			var name = $(caller).val();
			$(caller).before('<input name="'+$(caller).attr("id")+'[pillNames][]" type="hidden" value="'+name+'">');
			$(caller).before('\
				<div>\
					<div class="btn-group">\
						<button type="button" class="btn btn-default pillNameButton">'+name+'</button>\
						<button type="button" class="btn btn-danger" data-id="'+name+'" onClick="removePillName(this);">X</button>\
					</div>\
					<br/><br/>\
				</div>\
			');
			$(caller).prev().show(); //originally hidden by CSS
			$(caller).val("");
		}
	}
	
	/**
	 * Another helper function which is called when the new pill is added not
	 * with autocomplete. When user adds new pill via pressing ENTER or clicking
	 * "Add Pill" button this function is called to add new pill name into possibleNames
	 * array (to make the name appear during future autocomplete suggestions) and also 
	 * close autocomplete suggestions menu.
	 * @param {String} name The new entered pill name
	*/
	function customNameEntered(name){
		if(possibleNames.indexOf(name) == -1) //is a new name for suggestions
			possibleNames.push(name);
		pillField.autocomplete("close"); // close suggestions menu
	}
	
	/**
	 * Checks if the new entered name is allready selected/entered for specific cell.
	 * This is used to avoid duplicate pill names entered for a cell.
	 * @param {Element} caller Input element field where pill was entered
	 * @return {boolean} False if the new name is duplicate, True otherwise
	 */
	function isNewName(caller){
		var result = true
		$(caller).parents("td").find(".pillNameButton").each(function(index) {
			if(($(this).text()) == ($(caller).val())) {
				result = false;
				return false; //this only breaks each(), but doesn't return value from isNewName()
			}
		});
		return result;
	}
	
	/**
	 * Triggered when keyboard button pressed
	 */
	pillField.keypress(function(e) {
		if(e.which == 13) { //checking if ENTER pressed
			customNameEntered($(this).val());
			pillNameEntered(this);
			e.preventDefault();
		}
	});
	
	/**
	 * Triggered when Add Pill button clicked
	 */
	$('.addPillButton').click(function() {
		var field = $(this).prev().prev();
		customNameEntered(field.val());
		pillNameEntered(field);
	});

	//autocomplete initialisation
	pillField.autocomplete();
	pillField.autocomplete("option", "source", possibleNames);
	
	/**
	 * Triggered when selection is made using autocomplete
	 */
	pillField.on( "autocompleteselect", function( event, ui ) {
		$(this).val(ui.item.value);
		pillNameEntered(this);
		return false;
	});
	
	//always start showing only the first day - Monday. 
	var current = $("tbody tr").first().find("div");
	//using CSS all days are hidden
	current.show();
	
	/**
	 * Triggered when any day is pressed. Hides the old day
	 * and shows the new pressed one.
	 */
	$("tbody th").click(function() {
	  current.slideUp();
	  current = $(this).parent().find("div");
	  current.slideDown();
	});
	
	/**
	 * Triggered on each focus event in table's body
	 * tracking last focused element. "Copy from one to all"
	 * button might need last focused item.
	 */
	$("tbody *").not(".btn-danger").focus(function() {
		lastFocus = $(this);
	});
	
	/**
	 * Triggered when "Copy from one to all" button is clicked.
	 * Getting values from last focused item and copy those into
	 * all other remaining cells.
	 */
	$("#copyFromOneToAll").click(function() {
		if(lastFocus){
			//It is TD allready only when red button clicked
			if(lastFocus.prop("tagName") != "TD")
				lastFocus = lastFocus.parents("td");
			//time
			originValue = lastFocus.find('input[type="time"]').val();
			$('tbody input[type="time"]').val(originValue);
			//importance
			originValue = lastFocus.find('select').val();
			$('tbody select').val(originValue);
			//pillNames
			pillNames = lastFocus.find(".pillNameButton");
			//remove all existing names including in selected cell
			$(".pillNameButton").parent().parent().remove();
			$('input[type="hidden"]').remove();
			pillNames.each(function() {
				originValue = $(this).text();
				$('tbody input[type="text"]').each(function() {
					$(this).val(originValue);
					pillNameEntered(this);
				});
			});
		}
	});
	
	/**
	 * Triggered when "Copy from one day to all days" button
	 * is clicked. Getting values from last focused day and copy
	 * those values into all other days.
	 */
	$("#copyFromOneDayToAllDays").click(function() {
		if(lastFocus){
			// At first let's collect data which we need to copy
			var tr = lastFocus.parents("tr"); //day from where we copy all values
			var originDay = []; // values stored for whole day
			tr.find("td").each(function() {
				var td = {}; //values stored for one cell
				td.time = $(this).find('input[type="time"]').val();
				td.importance = $(this).find('select').val();
				td.names = [];
				$(this).find(".pillNameButton").each(function() {
					td.names.push($(this).text());
				});
				originDay.push(td);
			});
			console.log(originDay);
			//Copy all data
			$("td").each(function() {
				var id = $(this).find('input[type="text"]').attr("id");
				var index = id % 4; //cell index
				$(this).find('input[type="time"]').val(originDay[index].time);
				$(this).find("select").val(originDay[index].importance);
				//names
				$(this).find(".pillNameButton").parent().parent().remove();
				$(this).find('input[type="hidden"]').remove();
				for (var i = 0; i < originDay[index].names.length; i++){
					var input = $(this).find('input[type="text"]');
					input.val(originDay[index].names[i]);
					pillNameEntered(input);
				}
			});
		}
	});
});