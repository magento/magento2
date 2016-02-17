
$(function(){

	$('#options input').on('change',function(){
		var $this = $(this);
		if( $this.prop('checked') ){
			createCookie($this.attr('name'),1);
		}else{
			createCookie($this.attr('name'),0);
		}
	});


	function createCookie(name,value,days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		}
		else var expires = "";
		document.cookie = name+"="+value+expires+"; path=/";
	}

	function readCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	}

	function eraseCookie(name) {
		createCookie(name,"",-1);
	}


	function showdiff(){
		//show diff of this javascript object and the corresponding php object
		var php_area = $('#object_comparison');
		if( !php_area.length ){
			return;
		}
		php_area.hide();
		diffText(php_area.text(),obj_buffer);
	}
	showdiff();

	function SideBySideObjects(){
		var php = $('#object_comparison').hide().val().split('------------------------------------------------------------');
		var js = obj_buffer.split('------------------------------------------------------------');

		var table = '<table>';
		for(var i = 0; i < php.length; i++ ){
			table += '<tr><td>'+php[i]+'</td><td>'+js[i]+'</td></tr>';
		}
		table += '</table>';

		$('#objectdiff').html(table);
	}
	//SideBySideObjects();


});

var object_level = 0;
var obj_buffer = '';
var objects = [];
function obj(mixed){
	var keys = [], k, i, output = '', type, len;

	if( obj_buffer == '' ){
		obj_buffer = "----make sure caching is turned off----\n";
	}



	var exclude_keys = ['originalRuleset','currentFileInfo','_lookups','index'];//'variable','combinator'

	if( mixed == null ){
		output = '(NULL)';

	}else{

		type = typeof mixed;
		switch(type){
			case 'object':



				var t = mixed.constructor.name;
				if( t === 'Array' ){
					output += 'array(';
				}else{
					output += 'object';

					if( mixed.type ){
						output += ' '+mixed.type;
					}

					if( objects.indexOf(mixed) >= 0 ){
						return 'recursive';
					}
					objects.push(mixed);


					output += '(';

				}

				//output = mixed.constructor.name+' object(';
				//output = mixed.getName()+' object(';

				for(k in mixed){
					if( mixed.hasOwnProperty(k) && exclude_keys.indexOf(k) < 0 ){
						keys.push(k);
					}
				}

				keys.sort();
				len = keys.length;

				if( len == 0 ){
					output += ')';
					break;
				}


				output += "\n";
				for( i = 0; i < keys.length; i++){
					k = keys[i];
					object_level++;
					output += Array((object_level+1)).join('    ') + '[' + k + '] => ' + obj(mixed[k]) + "\n";
					object_level--;
				}
				output += Array((object_level+1)).join('    ')+')';
			break;
			case 'function':
			break;
			case 'string':
				output = '(string:' +mixed.length+')'+mixed+'';
			break;
			default:
				output = '('+type+')'+mixed;
			break;
		}
	}

	if( object_level === 0 ){
		obj_buffer += output +"\n------------------------------------------------------------\n";
		objects = [];
	}
	return output;
}

/**
 * Function to get an object's class type
 *
 */
Object.prototype.getName = function() {
   var funcNameRegex = /function (.{1,})\(/;
   var results = (funcNameRegex).exec((this).constructor.toString());
   return (results && results.length > 1) ? results[1] : "";
};


function diffText(txt1,txt2){

	function byId(id) { return document.getElementById(id); }

	var base = difflib.stringAsLines(txt1),
		newtxt = difflib.stringAsLines(txt2);
		sm = new difflib.SequenceMatcher(base, newtxt),
		opcodes = sm.get_opcodes(),
		diffoutputdiv = byId("objectdiff");

		diffoutputdiv.innerHTML = "";

	diffoutputdiv.appendChild(diffview.buildView({
		baseTextLines: base,
		newTextLines: newtxt,
		opcodes: opcodes,
		baseTextName: "Less.php",
		newTextName: "Less.js",
		//contextSize: contextSize,
		viewType: 0
	}));

}

function diffUsingJS(viewType, id1, id2) {
	"use strict";
	id1 = id1 || 'lessphp_textarea';
	id2 = id2 || 'lessjs_textarea';


	var byId = function (id) { return document.getElementById(id); },
		base = difflib.stringAsLines(byId(id1).value),
		newtxt = difflib.stringAsLines(byId(id2).value),
		sm = new difflib.SequenceMatcher(base, newtxt),
		opcodes = sm.get_opcodes(),
		diffoutputdiv = byId("diffoutput");
		//contextSize = byId("contextSize").value;

	diffoutputdiv.innerHTML = "";
	//contextSize = contextSize || null;

	diffoutputdiv.appendChild(diffview.buildView({
		baseTextLines: base,
		newTextLines: newtxt,
		opcodes: opcodes,
		baseTextName: "Less.php",
		newTextName: "Less.js",
		//contextSize: contextSize,
		viewType: viewType
	}));
}