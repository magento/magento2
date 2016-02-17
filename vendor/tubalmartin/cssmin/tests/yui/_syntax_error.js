
window.$ = $telerik.$;
$(document).ready(function() {
movePageElements();

var text = $('textarea').val();

if (text != "")
$('textarea').attr("style", "display: block;");
else
$('textarea').attr("style", "display: none;");

//cleanup
text = null;
});

function movePageElements() {
var num = null;
var pagenum = $(".pagecontrolscontainer");
if (pagenum.length > 0) {
var num = pagenum.attr("pagenumber");
if ((num > 5) && (num < 28)) {
var x = $('div#commentbutton');
$("div.buttonContainer").prepend(x);
}
else {
$('div#commentbutton').attr("style", "display: none;");
}
}

//Add in dropshadowing
if ((num > 5) && (num < 28)) {
var top = $('.dropshadow-top');
var middle = $('#dropshadow');
var bottom = $('.dropshadow-bottom');
$('#page').prepend(top);
$('#topcontainer').after(middle);
middle.append($('#topcontainer'));
middle.after(bottom);
}

//cleanup
num = null;
pagenum = null;
top = null;
middle = null;
bottom=null;
}

function expandCollapseDiv(id) {
$telerik.$(id).slideToggle("slow");
}

function expandCollapseHelp() {
$('.helpitems').slideToggle("slow");

//Add in dropshadowing
if ($('#helpcontainer').length) {
$('#help-dropshadow-bot').insertAfter('#helpcontainer');
$('#help-dropshadow-bot').removeAttr("style");
}
}

function expandCollapseComments() {
var style = $('textarea').attr("style");
if (style == "display: none;")
$('textarea').fadeIn().focus();
else
$('textarea').fadeOut();

//cleanup
style = null;
}
