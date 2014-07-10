jQuery(document).ready(function() {

	var dates = [];
	
	
	dates = jQuery('#mdpicker_dates').val().split(',');
	for(var i=dates.length; i--;) {
		dates[i] = +dates[i];
		if (!dates[i]) dates.splice(i,1);
	}
	
	jQuery('#mdp-datepicker').datepicker({
		dateFormat: '@',
		numberOfMonths: [2,3],
		showButtonPanel: false,
		beforeShowDay: is_day_selected,
		onSelect: select_day
	});
	
	
	function select_day(dateEpoch) {
		var i = jQuery.inArray(+dateEpoch,dates);
		if (~i)
			dates.splice(i, 1);
		else
			dates.push(+dateEpoch);
		jQuery('#mdpicker_dates').val(dates.join());
	}
	
	function is_day_selected(date) {
		return ( ~jQuery.inArray(date.getTime(),dates) ) ? [true, 'mdp-highlight'] : [true];
	}
	
});