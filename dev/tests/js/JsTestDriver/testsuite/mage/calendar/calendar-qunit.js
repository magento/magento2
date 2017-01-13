/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

test( "initialization", function() {
	var calendar = $('#calendar').calendar();
	ok( calendar.is(':mage-calendar'), "this test is fine" );
	calendar.calendar('destroy');
});
test( "global configuration merge", function() {
	$.extend(true, $, {
        calendarConfig: {
            showOn: 'button',
            showAnim: '',
            buttonImageOnly: true,
            showButtonPanel: true,
            showWeek: true,
            timeFormat: '',
            showTime: false,
            showHour: false,
            showMinute: false
        }
    });
    var calendar = $('#calendar').calendar();
    equal('button', calendar.calendar('option', 'showOn'));
    equal('', calendar.calendar('option', 'showAnim'));
    ok(calendar.calendar('option', 'buttonImageOnly'));
    ok(calendar.calendar('option', 'showButtonPanel'));
    ok(calendar.calendar('option', 'showWeek'));
    equal('', calendar.calendar('option', 'timeFormat'));
    equal(false, calendar.calendar('option', 'showTime'));
    equal(false, calendar.calendar('option', 'showHour'));
    equal(false, calendar.calendar('option', 'showMinute'));
	calendar.calendar('destroy');
	delete $.calendarConfig;
});
test( "specifying AM/PM in timeformat option changes AMPM option to true", function(){
	var calendar = $('#calendar').calendar({timeFormat: 'hh:mm tt', ampm: false});
    ok(calendar.calendar('option', 'ampm'));
    calendar.calendar('destroy');
});
test( "omitting AM/PM in timeformat option changes AMPM option to false", function(){
	var calendar = $('#calendar').calendar({timeFormat: 'hh:mm'});
    notEqual(true, calendar.calendar('option', 'ampm'));
    calendar.calendar('destroy');
});
test( "with server timezone offset", function(){
	var serverTimezoneSeconds = 1346122095,
	calendar = $('#calendar').calendar({serverTimezoneSeconds: serverTimezoneSeconds}),
        currentDate = new Date();
    currentDate.setTime((serverTimezoneSeconds + currentDate.getTimezoneOffset() * 60) * 1000);
    ok(currentDate.toString() === calendar.calendar('getTimezoneDate').toString());
    calendar.calendar('destroy');
});
test( "without sever timezone offset", function() {
	var calendar = $('#calendar').calendar(),
        currentDate = new Date();
    ok(currentDate.toString() === calendar.calendar('getTimezoneDate').toString());
    calendar.calendar('destroy');
});
test( "dateTime format conversions", function() {
	var calendar = $('#calendar').calendar({dateFormat: 'M/d/yy', timeFormat: 'h:mm a'});
    equal('mm/d/yy', calendar.calendar('option', 'dateFormat'));
    equal('h:mm tt', calendar.calendar('option', 'timeFormat'));
    calendar.calendar('destroy');
    calendar.calendar({dateFormat: 'MMMM/EEEE/yyyy', timeFormat: 'HH:mm'});
    equal('MM/DD/yy', calendar.calendar('option', 'dateFormat'));
    equal('hh:mm', calendar.calendar('option', 'timeFormat'));
    calendar.calendar('destroy');
});
test( "destroy", function() {
	var calendar = $('#calendar').calendar(),
        calendarExist = calendar.is(':mage-calendar');
    calendar.calendar('destroy');
    equal(true, calendarExist != calendar.is(':mage-calendar'));
});
