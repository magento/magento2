/*
 * Ext JS Library 1.1 Beta 1
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */


Ext.util.DelayedTask=function(fn,scope,args){var id=null,d,t;var call=function(){var now=new Date().getTime();if(now-t>=d){clearInterval(id);id=null;fn.apply(scope,args||[]);}};this.delay=function(delay,newFn,newScope,newArgs){if(id&&delay!=d){this.cancel();}
d=delay;t=new Date().getTime();fn=newFn||fn;scope=newScope||scope;args=newArgs||args;if(!id){id=setInterval(call,d);}};this.cancel=function(){if(id){clearInterval(id);id=null;}};};

Ext.util.MixedCollection=function(allowFunctions,keyFn){this.items=[];this.map={};this.keys=[];this.length=0;this.addEvents({"clear":true,"add":true,"replace":true,"remove":true,"sort":true});this.allowFunctions=allowFunctions===true;if(keyFn){this.getKey=keyFn;}
Ext.util.MixedCollection.superclass.constructor.call(this);};Ext.extend(Ext.util.MixedCollection,Ext.util.Observable,{allowFunctions:false,add:function(key,o){if(arguments.length==1){o=arguments[0];key=this.getKey(o);}
if(typeof key=="undefined"||key===null){this.length++;this.items.push(o);this.keys.push(null);}else{var old=this.map[key];if(old){return this.replace(key,o);}
this.length++;this.items.push(o);this.map[key]=o;this.keys.push(key);}
this.fireEvent("add",this.length-1,o,key);return o;},getKey:function(o){return o.id;},replace:function(key,o){if(arguments.length==1){o=arguments[0];key=this.getKey(o);}
var old=this.item(key);if(typeof key=="undefined"||key===null||typeof old=="undefined"){return this.add(key,o);}
var index=this.indexOfKey(key);this.items[index]=o;this.map[key]=o;this.fireEvent("replace",key,old,o);return o;},addAll:function(objs){if(arguments.length>1||objs instanceof Array){var args=arguments.length>1?arguments:objs;for(var i=0,len=args.length;i<len;i++){this.add(args[i]);}}else{for(var key in objs){if(this.allowFunctions||typeof objs[key]!="function"){this.add(objs[key],key);}}}},each:function(fn,scope){var items=[].concat(this.items);for(var i=0,len=items.length;i<len;i++){if(fn.call(scope||items[i],items[i],i,len)===false){break;}}},eachKey:function(fn,scope){for(var i=0,len=this.keys.length;i<len;i++){fn.call(scope||window,this.keys[i],this.items[i],i,len);}},find:function(fn,scope){for(var i=0,len=this.items.length;i<len;i++){if(fn.call(scope||window,this.items[i],this.keys[i])){return this.items[i];}}
return null;},insert:function(index,key,o){if(arguments.length==2){o=arguments[1];key=this.getKey(o);}
if(index>=this.length){return this.add(key,o);}
this.length++;this.items.splice(index,0,o);if(typeof key!="undefined"&&key!=null){this.map[key]=o;}
this.keys.splice(index,0,key);this.fireEvent("add",index,o,key);return o;},remove:function(o){return this.removeAt(this.indexOf(o));},removeAt:function(index){if(index<this.length&&index>=0){this.length--;var o=this.items[index];this.items.splice(index,1);var key=this.keys[index];if(typeof key!="undefined"){delete this.map[key];}
this.keys.splice(index,1);this.fireEvent("remove",o,key);}},removeKey:function(key){return this.removeAt(this.indexOfKey(key));},getCount:function(){return this.length;},indexOf:function(o){if(!this.items.indexOf){for(var i=0,len=this.items.length;i<len;i++){if(this.items[i]==o)return i;}
return-1;}else{return this.items.indexOf(o);}},indexOfKey:function(key){if(!this.keys.indexOf){for(var i=0,len=this.keys.length;i<len;i++){if(this.keys[i]==key)return i;}
return-1;}else{return this.keys.indexOf(key);}},item:function(key){var item=typeof this.map[key]!="undefined"?this.map[key]:this.items[key];return typeof item!='function'||this.allowFunctions?item:null;},itemAt:function(index){return this.items[index];},key:function(key){return this.map[key];},contains:function(o){return this.indexOf(o)!=-1;},containsKey:function(key){return typeof this.map[key]!="undefined";},clear:function(){this.length=0;this.items=[];this.keys=[];this.map={};this.fireEvent("clear");},first:function(){return this.items[0];},last:function(){return this.items[this.length-1];},_sort:function(property,dir,fn){var dsc=String(dir).toUpperCase()=="DESC"?-1:1;fn=fn||function(a,b){return a-b;};var c=[],k=this.keys,items=this.items;for(var i=0,len=items.length;i<len;i++){c[c.length]={key:k[i],value:items[i],index:i};}
c.sort(function(a,b){var v=fn(a[property],b[property])*dsc;if(v==0){v=(a.index<b.index?-1:1);}
return v;});for(var i=0,len=c.length;i<len;i++){items[i]=c[i].value;k[i]=c[i].key;}
this.fireEvent("sort",this);},sort:function(dir,fn){this._sort("value",dir,fn);},keySort:function(dir,fn){this._sort("key",dir,fn||function(a,b){return String(a).toUpperCase()-String(b).toUpperCase();});},getRange:function(start,end){var items=this.items;if(items.length<1){return[];}
start=start||0;end=Math.min(typeof end=="undefined"?this.length-1:end,this.length-1);var r=[];if(start<=end){for(var i=start;i<=end;i++){r[r.length]=items[i];}}else{for(var i=start;i>=end;i--){r[r.length]=items[i];}}
return r;},filter:function(property,value){if(!value.exec){value=String(value);if(value.length==0){return this.clone();}
value=new RegExp("^"+Ext.escapeRe(value),"i");}
return this.filterBy(function(o){return o&&value.test(o[property]);});},filterBy:function(fn,scope){var r=new Ext.util.MixedCollection();r.getKey=this.getKey;var k=this.keys,it=this.items;for(var i=0,len=it.length;i<len;i++){if(fn.call(scope||this,it[i],k[i])){r.add(k[i],it[i]);}}
return r;},clone:function(){var r=new Ext.util.MixedCollection();var k=this.keys,it=this.items;for(var i=0,len=it.length;i<len;i++){r.add(k[i],it[i]);}
r.getKey=this.getKey;return r;}});Ext.util.MixedCollection.prototype.get=Ext.util.MixedCollection.prototype.item;

Ext.util.JSON=new(function(){var useHasOwn={}.hasOwnProperty?true:false;var pad=function(n){return n<10?"0"+n:n;};var m={"\b":'\\b',"\t":'\\t',"\n":'\\n',"\f":'\\f',"\r":'\\r','"':'\\"',"\\":'\\\\'};var encodeString=function(s){if(/["\\\x00-\x1f]/.test(s)){return'"'+s.replace(/([\x00-\x1f\\"])/g,function(a,b){var c=m[b];if(c){return c;}
c=b.charCodeAt();return"\\u00"+
Math.floor(c/16).toString(16)+
(c%16).toString(16);})+'"';}
return'"'+s+'"';};var encodeArray=function(o){var a=["["],b,i,l=o.length,v;for(i=0;i<l;i+=1){v=o[i];switch(typeof v){case"undefined":case"function":case"unknown":break;default:if(b){a.push(',');}
a.push(v===null?"null":Ext.util.JSON.encode(v));b=true;}}
a.push("]");return a.join("");};var encodeDate=function(o){return'"'+o.getFullYear()+"-"+
pad(o.getMonth()+1)+"-"+
pad(o.getDate())+"T"+
pad(o.getHours())+":"+
pad(o.getMinutes())+":"+
pad(o.getSeconds())+'"';};this.encode=function(o){if(typeof o=="undefined"||o===null){return"null";}else if(o instanceof Array){return encodeArray(o);}else if(o instanceof Date){return encodeDate(o);}else if(typeof o=="string"){return encodeString(o);}else if(typeof o=="number"){return isFinite(o)?String(o):"null";}else if(typeof o=="boolean"){return String(o);}else{var a=["{"],b,i,v;for(i in o){if(!useHasOwn||o.hasOwnProperty(i)){v=o[i];switch(typeof v){case"undefined":case"function":case"unknown":break;default:if(b){a.push(',');}
a.push(this.encode(i),":",v===null?"null":this.encode(v));b=true;}}}
a.push("}");return a.join("");}};this.decode=function(json){return eval("("+json+')');};})();Ext.encode=Ext.util.JSON.encode;Ext.decode=Ext.util.JSON.decode;

Ext.util.Format=function(){var trimRe=/^\s+|\s+$/g;return{ellipsis:function(value,len){if(value&&value.length>len){return value.substr(0,len-3)+"...";}
return value;},undef:function(value){return typeof value!="undefined"?value:"";},htmlEncode:function(value){return!value?value:String(value).replace(/&/g,"&amp;").replace(/>/g,"&gt;").replace(/</g,"&lt;").replace(/"/g,"&quot;");},trim:function(value){return String(value).replace(trimRe,"");},substr:function(value,start,length){return String(value).substr(start,length);},lowercase:function(value){return String(value).toLowerCase();},uppercase:function(value){return String(value).toUpperCase();},capitalize:function(value){return!value?value:value.charAt(0).toUpperCase()+value.substr(1).toLowerCase();},call:function(value,fn){if(arguments.length>2){var args=Array.prototype.slice.call(arguments,2);args.unshift(value);return eval(fn).apply(window,args);}else{return eval(fn).call(window,value);}},usMoney:function(v){v=(Math.round((v-0)*100))/100;v=(v==Math.floor(v))?v+".00":((v*10==Math.floor(v*10))?v+"0":v);return"$"+v;},date:function(v,format){if(!v){return"";}
if(!(v instanceof Date)){v=new Date(Date.parse(v));}
return v.dateFormat(format||"m/d/Y");},dateRenderer:function(format){return function(v){return Ext.util.Format.date(v,format);};},stripTagsRE:/<\/?[^>]+>/gi,stripTags:function(v){return!v?v:String(v).replace(this.stripTagsRE,"");}};}();

Ext.util.CSS=function(){var rules=null;var doc=document;var camelRe=/(-[a-z])/gi;var camelFn=function(m,a){return a.charAt(1).toUpperCase();};return{createStyleSheet:function(cssText){var ss;if(Ext.isIE){ss=doc.createStyleSheet();ss.cssText=cssText;}else{var head=doc.getElementsByTagName("head")[0];var rules=doc.createElement("style");rules.setAttribute("type","text/css");try{rules.appendChild(doc.createTextNode(cssText));}catch(e){rules.cssText=cssText;}
head.appendChild(rules);ss=rules.styleSheet?rules.styleSheet:(rules.sheet||doc.styleSheets[doc.styleSheets.length-1]);}
this.cacheStyleSheet(ss);return ss;},removeStyleSheet:function(id){var existing=doc.getElementById(id);if(existing){existing.parentNode.removeChild(existing);}},swapStyleSheet:function(id,url){this.removeStyleSheet(id);var ss=doc.createElement("link");ss.setAttribute("rel","stylesheet");ss.setAttribute("type","text/css");ss.setAttribute("id",id);ss.setAttribute("href",url);doc.getElementsByTagName("head")[0].appendChild(ss);},refreshCache:function(){return this.getRules(true);},cacheStyleSheet:function(ss){if(!rules){rules={};}
try{var ssRules=ss.cssRules||ss.rules;for(var j=ssRules.length-1;j>=0;--j){rules[ssRules[j].selectorText]=ssRules[j];}}catch(e){}},getRules:function(refreshCache){if(rules==null||refreshCache){rules={};var ds=doc.styleSheets;for(var i=0,len=ds.length;i<len;i++){try{this.cacheStyleSheet(ds[i]);}catch(e){}}}
return rules;},getRule:function(selector,refreshCache){var rs=this.getRules(refreshCache);if(!(selector instanceof Array)){return rs[selector];}
for(var i=0;i<selector.length;i++){if(rs[selector[i]]){return rs[selector[i]];}}
return null;},updateRule:function(selector,property,value){if(!(selector instanceof Array)){var rule=this.getRule(selector);if(rule){rule.style[property.replace(camelRe,camelFn)]=value;return true;}}else{for(var i=0;i<selector.length;i++){if(this.updateRule(selector[i],property,value)){return true;}}}
return false;}};}();

Ext.util.ClickRepeater=function(el,config)
{this.el=Ext.get(el);this.el.unselectable();Ext.apply(this,config);this.addEvents({"mousedown":true,"click":true,"mouseup":true});this.el.on("mousedown",this.handleMouseDown,this);if(this.preventDefault||this.stopDefault){this.el.on("click",function(e){if(this.preventDefault){e.preventDefault();}
if(this.stopDefault){e.stopEvent();}},this);}
if(this.handler){this.on("click",this.handler,this.scope||this);}
Ext.util.ClickRepeater.superclass.constructor.call(this);};Ext.extend(Ext.util.ClickRepeater,Ext.util.Observable,{interval:20,delay:250,preventDefault:true,stopDefault:false,timer:0,docEl:Ext.get(document),handleMouseDown:function(){clearTimeout(this.timer);this.el.blur();if(this.pressClass){this.el.addClass(this.pressClass);}
this.mousedownTime=new Date();this.docEl.on("mouseup",this.handleMouseUp,this);this.el.on("mouseout",this.handleMouseOut,this);this.fireEvent("mousedown",this);this.fireEvent("click",this);this.timer=this.click.defer(this.delay||this.interval,this);},click:function(){this.fireEvent("click",this);this.timer=this.click.defer(this.getInterval(),this);},getInterval:function(){if(!this.accelerate){return this.interval;}
var pressTime=this.mousedownTime.getElapsed();if(pressTime<500){return 400;}else if(pressTime<1700){return 320;}else if(pressTime<2600){return 250;}else if(pressTime<3500){return 180;}else if(pressTime<4400){return 140;}else if(pressTime<5300){return 80;}else if(pressTime<6200){return 50;}else{return 10;}},handleMouseOut:function(){clearTimeout(this.timer);if(this.pressClass){this.el.removeClass(this.pressClass);}
this.el.on("mouseover",this.handleMouseReturn,this);},handleMouseReturn:function(){this.el.un("mouseover",this.handleMouseReturn);if(this.pressClass){this.el.addClass(this.pressClass);}
this.click();},handleMouseUp:function(){clearTimeout(this.timer);this.el.un("mouseover",this.handleMouseReturn);this.el.un("mouseout",this.handleMouseOut);this.docEl.un("mouseup",this.handleMouseUp);this.el.removeClass(this.pressClass);this.fireEvent("mouseup",this);}});

Ext.KeyNav=function(el,config){this.el=Ext.get(el);Ext.apply(this,config);if(!this.disabled){this.disabled=true;this.enable();}};Ext.KeyNav.prototype={disabled:false,defaultEventAction:"stopEvent",prepareEvent:function(e){var k=e.getKey();var h=this.keyToHandler[k];if(Ext.isSafari&&h&&k>=37&&k<=40){e.stopEvent();}},relay:function(e){var k=e.getKey();var h=this.keyToHandler[k];if(h&&this[h]){if(this.doRelay(e,this[h],h)!==true){e[this.defaultEventAction]();}}},doRelay:function(e,h,hname){return h.call(this.scope||this,e);},enter:false,left:false,right:false,up:false,down:false,tab:false,esc:false,pageUp:false,pageDown:false,del:false,home:false,end:false,keyToHandler:{37:"left",39:"right",38:"up",40:"down",33:"pageUp",34:"pageDown",46:"del",36:"home",35:"end",13:"enter",27:"esc",9:"tab"},enable:function(){if(this.disabled){if(Ext.isIE){this.el.on("keydown",this.relay,this);}else{this.el.on("keydown",this.prepareEvent,this);this.el.on("keypress",this.relay,this);}
this.disabled=false;}},disable:function(){if(!this.disabled){if(Ext.isIE){this.el.un("keydown",this.relay);}else{this.el.un("keydown",this.prepareEvent);this.el.un("keypress",this.relay);}
this.disabled=true;}}};

Ext.KeyMap=function(el,config,eventName){this.el=Ext.get(el);this.eventName=eventName||"keydown";this.bindings=[];if(config instanceof Array){for(var i=0,len=config.length;i<len;i++){this.addBinding(config[i]);}}else{this.addBinding(config);}
this.keyDownDelegate=Ext.EventManager.wrap(this.handleKeyDown,this,true);this.enable();};Ext.KeyMap.prototype={stopEvent:false,addBinding:function(config){var keyCode=config.key,shift=config.shift,ctrl=config.ctrl,alt=config.alt,fn=config.fn,scope=config.scope;if(typeof keyCode=="string"){var ks=[];var keyString=keyCode.toUpperCase();for(var j=0,len=keyString.length;j<len;j++){ks.push(keyString.charCodeAt(j));}
keyCode=ks;}
var keyArray=keyCode instanceof Array;var handler=function(e){if((!shift||e.shiftKey)&&(!ctrl||e.ctrlKey)&&(!alt||e.altKey)){var k=e.getKey();if(keyArray){for(var i=0,len=keyCode.length;i<len;i++){if(keyCode[i]==k){if(this.stopEvent){e.stopEvent();}
fn.call(scope||window,k,e);return;}}}else{if(k==keyCode){if(this.stopEvent){e.stopEvent();}
fn.call(scope||window,k,e);}}}};this.bindings.push(handler);},handleKeyDown:function(e){if(this.enabled){var b=this.bindings;for(var i=0,len=b.length;i<len;i++){b[i].call(this,e);}}},isEnabled:function(){return this.enabled;},enable:function(){if(!this.enabled){this.el.on(this.eventName,this.keyDownDelegate);this.enabled=true;}},disable:function(){if(this.enabled){this.el.removeListener(this.eventName,this.keyDownDelegate);this.enabled=false;}}};

Ext.util.TextMetrics=function(){var shared;return{measure:function(el,text,fixedWidth){if(!shared){shared=Ext.util.TextMetrics.Instance(el,fixedWidth);}
shared.bind(el);shared.setFixedWidth(fixedWidth||'auto');return shared.getSize(text);},createInstance:function(el,fixedWidth){return Ext.util.TextMetrics.Instance(el,fixedWidth);}};}();Ext.util.TextMetrics.Instance=function(bindTo,fixedWidth){var ml=new Ext.Element(document.createElement('div'));document.body.appendChild(ml.dom);ml.position('absolute');ml.setLeftTop(-1000,-1000);ml.hide();if(fixedWidth){ml.setWidth(fixedWidth);}
var instance={getSize:function(text){ml.update(text);var s=ml.getSize();ml.update('');return s;},bind:function(el){ml.setStyle(Ext.fly(el).getStyles('font-size','font-style','font-weight','font-family','line-height'));},setFixedWidth:function(width){ml.setWidth(width);},getWidth:function(text){ml.dom.style.width='auto';return this.getSize(text).width;},getHeight:function(text){return this.getSize(text).height;}};instance.bind(bindTo);return instance;};Ext.Element.measureText=Ext.util.TextMetrics.measure;
