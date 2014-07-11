jQuery(document).ready(function() {

	var dates = [];
	
	
	dates = jQuery('#mdpicker_dates').val().split(',');
	for(var i=dates.length; i--;) if (!dates[i]) dates.splice(i,1);
	
	jQuery('#mdp-datepicker').datepicker({
		dateFormat: 'm/d/yy',
		numberOfMonths: [2,3],
		showButtonPanel: false,
		beforeShowDay: is_day_selected,
		onSelect: select_day
	});
	
	
	function select_day(date_string) {
		var i = jQuery.inArray(date_string,dates);
		if (~i)
			dates.splice(i, 1);
		else
			dates.push(date_string);
		jQuery('#mdpicker_dates').val(dates.join());
	}
	
	function is_day_selected(date) {
		date_string = (date.getMonth()+1)+'/'+date.getDate()+'/'+date.getFullYear();
		return ( ~jQuery.inArray(date_string,dates) ) ? [true, 'mdp-highlight'] : [true];
	}
	
});