/* German/Austrian initialisation for the jQuery UI date picker plugin. */
/* Based on the de initialisation. */

( function( factory ) {
	"use strict";

	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define( [ "../widgets/datepicker" ], factory );
	} else {

		// Browser globals
		factory( jQuery.datepicker );
	}
} )( function( datepicker ) {
"use strict";

datepicker.regional[ "de-AT" ] = {
	closeText: "Schließen",
	prevText: "&#x3C;Zurück",
	nextText: "Vor&#x3E;",
	currentText: "Heute",
	monthNames: [ "Jänner", "Februar", "März", "April", "Mai", "Juni",
	"Juli", "August", "September", "Oktober", "November", "Dezember" ],
	monthNamesShort: [ "Jän", "Feb", "Mär", "Apr", "Mai", "Jun",
	"Jul", "Aug", "Sep", "Okt", "Nov", "Dez" ],
	dayNames: [ "Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag" ],
	dayNamesShort: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ],
	dayNamesMin: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ],
	weekHeader: "KW",
	dateFormat: "dd.mm.yy",
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: "" };
datepicker.setDefaults( datepicker.regional[ "de-AT" ] );

return datepicker.regional[ "de-AT" ];

} );
