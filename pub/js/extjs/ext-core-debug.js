/*
 * Ext JS Library 1.1 Beta 1
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */


Ext.DomHelper = function(){
    var tempTableEl = null;
    var emptyTags = /^(?:br|frame|hr|img|input|link|meta|range|spacer|wbr|area|param|col)$/i;
           
    // build as innerHTML where available
    
    var createHtml = function(o){
        if(typeof o == 'string'){
            return o;
        }
        var b = "";
        if(!o.tag){
            o.tag = "div";
        }
        b += "<" + o.tag;
        for(var attr in o){
            if(attr == "tag" || attr == "children" || attr == "cn" || attr == "html" || typeof o[attr] == "function") continue;
            if(attr == "style"){
                var s = o["style"];
                if(typeof s == "function"){
                    s = s.call();
                }
                if(typeof s == "string"){
                    b += ' style="' + s + '"';
                }else if(typeof s == "object"){
                    b += ' style="';
                    for(var key in s){
                        if(typeof s[key] != "function"){
                            b += key + ":" + s[key] + ";";
                        }
                    }
                    b += '"';
                }
            }else{
                if(attr == "cls"){
                    b += ' class="' + o["cls"] + '"';
                }else if(attr == "htmlFor"){
                    b += ' for="' + o["htmlFor"] + '"';
                }else{
                    b += " " + attr + '="' + o[attr] + '"';
                }
            }
        }
        if(emptyTags.test(o.tag)){
            b += "/>";
        }else{
            b += ">";
            var cn = o.children || o.cn;
            if(cn){
                if(cn instanceof Array){
                    for(var i = 0, len = cn.length; i < len; i++) {
                        b += createHtml(cn[i], b);
                    }
                }else{
                    b += createHtml(cn, b);
                }
            }
            if(o.html){
                b += o.html;
            }
            b += "</" + o.tag + ">";
        }
        return b;
    };
    
    // build as dom
    
    var createDom = function(o, parentNode){
        var el = document.createElement(o.tag);
        var useSet = el.setAttribute ? true : false; // In IE some elements don't have setAttribute
        for(var attr in o){
            if(attr == "tag" || attr == "children" || attr == "cn" || attr == "html" || attr == "style" || typeof o[attr] == "function") continue;
            if(attr=="cls"){
                el.className = o["cls"];
            }else{
                if(useSet) el.setAttribute(attr, o[attr]);
                else el[attr] = o[attr];
            }
        }
        Ext.DomHelper.applyStyles(el, o.style);
        var cn = o.children || o.cn;
        if(cn){
            if(cn instanceof Array){
                for(var i = 0, len = cn.length; i < len; i++) {
                    createDom(cn[i], el);
                }
            }else{
                createDom(cn, el);
            }
        }
        if(o.html){
            el.innerHTML = o.html;
        }
        if(parentNode){
           parentNode.appendChild(el);
        }
        return el;
    };

    var ieTable = function(depth, s, h, e){
        tempTableEl.innerHTML = [s, h, e].join('');
        var i = -1, el = tempTableEl;
        while(++i < depth){
            el = el.firstChild;
        }
        return el;
    };

    // kill repeat to save bytes
    var ts = '<table>',
        te = '</table>',
        tbs = ts+'<tbody>',
        tbe = '</tbody>'+te,
        trs = tbs + '<tr>',
        tre = '</tr>'+tbe;

    
    var insertIntoTable = function(tag, where, el, html){
        if(!tempTableEl){
            tempTableEl = document.createElement('div');
        }
        var node;
        var before = null;
        if(tag == 'td'){
            if(where == 'afterbegin' || where == 'beforeend'){ // INTO a TD
                return;
            }
            if(where == 'beforebegin'){
                before = el;
                el = el.parentNode;
            } else{
                before = el.nextSibling;
                el = el.parentNode;
            }
            node = ieTable(4, trs, html, tre);
        }
        else if(tag == 'tr'){
            if(where == 'beforebegin'){
                before = el;
                el = el.parentNode;
                node = ieTable(3, tbs, html, tbe);
            } else if(where == 'afterend'){
                before = el.nextSibling;
                el = el.parentNode;
                node = ieTable(3, tbs, html, tbe);
            } else{ // INTO a TR
                if(where == 'afterbegin'){
                    before = el.firstChild;
                }
                node = ieTable(4, trs, html, tre);
            }
        } else if(tag == 'tbody'){
            if(where == 'beforebegin'){
                before = el;
                el = el.parentNode;
                node = ieTable(2, ts, html, te);
            } else if(where == 'afterend'){
                before = el.nextSibling;
                el = el.parentNode;
                node = ieTable(2, ts, html, te);
            } else{
                if(where == 'afterbegin'){
                    before = el.firstChild;
                }
                node = ieTable(3, tbs, html, tbe);
            }
        } else{ // TABLE
            if(where == 'beforebegin' || where == 'afterend'){ // OUTSIDE the table
                return;
            }
            if(where == 'afterbegin'){
                before = el.firstChild;
            }
            node = ieTable(2, ts, html, te);
        }
        el.insertBefore(node, before);
        return node;
    };
    
    return {
    
    useDom : false,

    
    markup : function(o){
        return createHtml(o);
    },

    
    applyStyles : function(el, styles){
        if(styles){
           el = Ext.fly(el);
           if(typeof styles == "string"){
               var re = /\s?([a-z\-]*)\:\s?([^;]*);?/gi;
               var matches;
               while ((matches = re.exec(styles)) != null){
                   el.setStyle(matches[1], matches[2]);
               }
           }else if (typeof styles == "object"){
               for (var style in styles){
                  el.setStyle(style, styles[style]);
               }
           }else if (typeof styles == "function"){
                Ext.DomHelper.applyStyles(el, styles.call());
           }
        }
    }, 
    
    
    insertHtml : function(where, el, html){
        where = where.toLowerCase();
        if(el.insertAdjacentHTML){
            var tag = el.tagName.toLowerCase();
            if(tag == "table" || tag == "tbody" || tag == "tr" || tag == 'td'){
                var rs;
                if(rs = insertIntoTable(tag, where, el, html)){
                    return rs;
                }
            }
            switch(where){
                case "beforebegin":
                    el.insertAdjacentHTML(where, html);
                    return el.previousSibling;
                case "afterbegin":
                    el.insertAdjacentHTML(where, html);
                    return el.firstChild;
                case "beforeend":
                    el.insertAdjacentHTML(where, html);
                    return el.lastChild;
                case "afterend":
                    el.insertAdjacentHTML(where, html);
                    return el.nextSibling;
            }
            throw 'Illegal insertion point -> "' + where + '"';
        }
        var range = el.ownerDocument.createRange();
        var frag;
        switch(where){
             case "beforebegin":
                range.setStartBefore(el);
                frag = range.createContextualFragment(html);
                el.parentNode.insertBefore(frag, el);
                return el.previousSibling;
             case "afterbegin":
                if(el.firstChild){
                    range.setStartBefore(el.firstChild);
                    frag = range.createContextualFragment(html);
                    el.insertBefore(frag, el.firstChild);
                    return el.firstChild;
                }else{
                    el.innerHTML = html;
                    return el.firstChild;
                }
            case "beforeend":
                if(el.lastChild){
                    range.setStartAfter(el.lastChild);
                    frag = range.createContextualFragment(html);
                    el.appendChild(frag);
                    return el.lastChild;
                }else{
                    el.innerHTML = html;
                    return el.lastChild;
                }
            case "afterend":
                range.setStartAfter(el);
                frag = range.createContextualFragment(html);
                el.parentNode.insertBefore(frag, el.nextSibling);
                return el.nextSibling;
            }
            throw 'Illegal insertion point -> "' + where + '"';
    },
    
    
    insertBefore : function(el, o, returnElement){
        return this.doInsert(el, o, returnElement, "beforeBegin");
    },
    
    
    insertAfter : function(el, o, returnElement){
        return this.doInsert(el, o, returnElement, "afterEnd", "nextSibling");
    },

    
    insertFirst : function(el, o, returnElement){
        return this.doInsert(el, o, returnElement, "afterBegin");
    },

    // private
    doInsert : function(el, o, returnElement, pos, sibling){
        el = Ext.getDom(el);
        var newNode;
        if(this.useDom){
            newNode = createDom(o, null);
            el.parentNode.insertBefore(newNode, sibling ? el[sibling] : el);
        }else{
            var html = createHtml(o);
            newNode = this.insertHtml(pos, el, html);
        }
        return returnElement ? Ext.get(newNode, true) : newNode;
    },

    
    append : function(el, o, returnElement){
        el = Ext.getDom(el);
        var newNode;
        if(this.useDom){
            newNode = createDom(o, null);
            el.appendChild(newNode);
        }else{
            var html = createHtml(o);
            newNode = this.insertHtml("beforeEnd", el, html);
        }
        return returnElement ? Ext.get(newNode, true) : newNode;
    },
    
    
    overwrite : function(el, o, returnElement){
        el = Ext.getDom(el);
        el.innerHTML = createHtml(o);
        return returnElement ? Ext.get(el.firstChild, true) : el.firstChild;
    },
    
    
    createTemplate : function(o){
        var html = createHtml(o);
        return new Ext.Template(html);
    }
    };
}();


Ext.Template = function(html){
    if(html instanceof Array){
        html = html.join("");
    }else if(arguments.length > 1){
        html = Array.prototype.join.call(arguments, "");
    }
    
    this.html = html;
    
};
Ext.Template.prototype = {
    
    applyTemplate : function(values){
        if(this.compiled){
            return this.compiled(values);
        }
        var useF = this.disableFormats !== true;
        var fm = Ext.util.Format, tpl = this;
        var fn = function(m, name, format, args){
            if(format && useF){
                if(format.substr(0, 5) == "this."){
                    return tpl.call(format.substr(5), values[name], values);
                }else{
                    if(args){
                        // quoted values are required for strings in compiled templates, 
                        // but for non compiled we need to strip them
                        // quoted reversed for jsmin
                        var re = /^\s*['"](.*)["']\s*$/;
                        args = args.split(',');
                        for(var i = 0, len = args.length; i < len; i++){
                            args[i] = args[i].replace(re, "$1");
                        }
                        args = [values[name]].concat(args);
                    }else{
                        args = [values[name]];
                    }
                    return fm[format].apply(fm, args);
                }
            }else{
                return values[name] !== undefined ? values[name] : "";
            }
        };
        return this.html.replace(this.re, fn);
    },
    
    
    set : function(html, compile){
        this.html = html;
        this.compiled = null;
        if(compile){
            this.compile();
        }
        return this;
    },
    
    
    disableFormats : false,
    
    
    re : /\{([\w-]+)(?:\:([\w\.]*)(?:\((.*?)?\))?)?\}/g,
    
    
    compile : function(){
        var fm = Ext.util.Format;
        var useF = this.disableFormats !== true;
        var sep = Ext.isGecko ? "+" : ",";
        var fn = function(m, name, format, args){
            if(format && useF){
                args = args ? ',' + args : "";
                if(format.substr(0, 5) != "this."){
                    format = "fm." + format + '(';
                }else{
                    format = 'this.call("'+ format.substr(5) + '", ';
                    args = ", values";
                }
            }else{
                args= ''; format = "(values['" + name + "'] == undefined ? '' : ";
            }
            return "'"+ sep + format + "values['" + name + "']" + args + ")"+sep+"'";
        };
        var body;
        // branched to use + in gecko and [].join() in others
        if(Ext.isGecko){
            body = "this.compiled = function(values){ return '" +
                   this.html.replace(/(\r\n|\n)/g, '\\n').replace(/'/g, "\\'").replace(this.re, fn) +
                    "';};";
        }else{
            body = ["this.compiled = function(values){ return ['"];
            body.push(this.html.replace(/(\r\n|\n)/g, '\\n').replace("'", "\\'").replace(this.re, fn));
            body.push("'].join('');};");
            body = body.join('');
        }
        eval(body);
        return this;
    },
    
    // private function used to call members
    call : function(fnName, value, allValues){
        return this[fnName](value, allValues);
    },
    
    
    insertFirst: function(el, values, returnElement){
        return this.doInsert('afterBegin', el, values, returnElement);
    },

    
    insertBefore: function(el, values, returnElement){
        return this.doInsert('beforeBegin', el, values, returnElement);
    },

    
    insertAfter : function(el, values, returnElement){
        return this.doInsert('afterEnd', el, values, returnElement);
    },
    
    
    append : function(el, values, returnElement){
        return this.doInsert('beforeEnd', el, values, returnElement);
    },

    doInsert : function(where, el, values, returnEl){
        el = Ext.getDom(el);
        var newNode = Ext.DomHelper.insertHtml(where, el, this.applyTemplate(values));
        return returnEl ? Ext.get(newNode, true) : newNode;
    },

    
    overwrite : function(el, values, returnElement){
        el = Ext.getDom(el);
        el.innerHTML = this.applyTemplate(values);
        return returnElement ? Ext.get(el.firstChild, true) : el.firstChild;
    }
};

Ext.Template.prototype.apply = Ext.Template.prototype.applyTemplate;

// backwards compat
Ext.DomHelper.Template = Ext.Template;


Ext.Template.from = function(el){
    el = Ext.getDom(el);
    return new Ext.Template(el.value || el.innerHTML);  
};


Ext.MasterTemplate = function(){
    Ext.MasterTemplate.superclass.constructor.apply(this, arguments);
    this.originalHtml = this.html;
    var st = {};
    var m, re = this.subTemplateRe;
    re.lastIndex = 0;
    var subIndex = 0;
    while(m = re.exec(this.html)){
        var name = m[1], content = m[2];
        st[subIndex] = {
            name: name,
            index: subIndex,
            buffer: [],
            tpl : new Ext.Template(content)
        };
        if(name){
            st[name] = st[subIndex];
        }
        st[subIndex].tpl.compile();
        st[subIndex].tpl.call = this.call.createDelegate(this);
        subIndex++;
    }
    this.subCount = subIndex;
    this.subs = st;
};
Ext.extend(Ext.MasterTemplate, Ext.Template, {
    
    subTemplateRe : /<tpl(?:\sname="([\w-]+)")?>((?:.|\n)*?)<\/tpl>/gi,
    
    
     add : function(name, values){
        if(arguments.length == 1){
            values = arguments[0];
            name = 0;
        }
        var s = this.subs[name];
        s.buffer[s.buffer.length] = s.tpl.apply(values);
        return this;
    },
    
    
    fill : function(name, values, reset){
        var a = arguments;
        if(a.length == 1 || (a.length == 2 && typeof a[1] == "boolean")){
            values = a[0];
            name = 0;
            reset = a[1];
        }
        if(reset){
            this.reset();
        }
        for(var i = 0, len = values.length; i < len; i++){
            this.add(name, values[i]);
        }
        return this;
    },
    
    
     reset : function(){
        var s = this.subs;
        for(var i = 0; i < this.subCount; i++){
            s[i].buffer = [];
        }
        return this;
    },
    
    applyTemplate : function(values){
        var s = this.subs;
        var replaceIndex = -1;
        this.html = this.originalHtml.replace(this.subTemplateRe, function(m, name){
            return s[++replaceIndex].buffer.join("");
        });
        return Ext.MasterTemplate.superclass.applyTemplate.call(this, values);
    },
    
    apply : function(){
        return this.applyTemplate.apply(this, arguments);
    },
    
    compile : function(){return this;}
});


Ext.MasterTemplate.prototype.addAll = Ext.MasterTemplate.prototype.fill;
 
Ext.MasterTemplate.from = function(el){
    el = Ext.getDom(el);
    return new Ext.MasterTemplate(el.value || el.innerHTML);  
};


Ext.DomQuery = function(){
    var cache = {}, simpleCache = {}, valueCache = {};
    var nonSpace = /\S/;
    var trimRe = /^\s+|\s+$/g;
    var tplRe = /\{(\d+)\}/g;
    var modeRe = /^(\s?[\/>]\s?|\s|$)/;
    var tagTokenRe = /^(#)?([\w-\*]+)/;
    
    function child(p, index){
        var i = 0;
        var n = p.firstChild;
        while(n){
            if(n.nodeType == 1){
               if(++i == index){
                   return n;
               }
            }
            n = n.nextSibling;
        }
        return null;
    };
    
    function next(n){
        while((n = n.nextSibling) && n.nodeType != 1);
        return n;
    };
    
    function prev(n){
        while((n = n.previousSibling) && n.nodeType != 1);
        return n;
    };
    
    function clean(d){
        var n = d.firstChild, ni = -1;
 	    while(n){
 	        var nx = n.nextSibling;
 	        if(n.nodeType == 3 && !nonSpace.test(n.nodeValue)){
 	            d.removeChild(n);
 	        }else{
 	            n.nodeIndex = ++ni;
 	        }
 	        n = nx;
 	    }
 	    return this;
 	};

    function byClassName(c, a, v, re, cn){
        if(!v){
            return c;
        }
        var r = [];
        for(var i = 0, ci; ci = c[i]; i++){
            cn = ci.className;
            if(cn && (' '+cn+' ').indexOf(v) != -1){
                r[r.length] = ci;
            }
        }
        return r;
    };

    function attrValue(n, attr){
        if(!n.tagName && typeof n.length != "undefined"){
            n = n[0];
        }
        if(!n){
            return null;
        }
        if(attr == "for"){
            return n.htmlFor;
        }
        if(attr == "class" || attr == "className"){
            return n.className;
        }
        return n.getAttribute(attr) || n[attr];
          
    };
    
    function getNodes(ns, mode, tagName){
        var result = [], cs;
        if(!ns){
            return result;
        }
        mode = mode ? mode.replace(trimRe, "") : "";
        tagName = tagName || "*";
        if(typeof ns.getElementsByTagName != "undefined"){
            ns = [ns];   
        }
        if(mode != "/" && mode != ">"){
            for(var i = 0, ni; ni = ns[i]; i++){
                cs = ni.getElementsByTagName(tagName);
                for(var j = 0, ci; ci = cs[j]; j++){
                    result[result.length] = ci;
                }
            }
        }else{
            for(var i = 0, ni; ni = ns[i]; i++){
                var cn = ni.getElementsByTagName(tagName);
                for(var j = 0, cj; cj = cn[j]; j++){
                    if(cj.parentNode == ni){
                        result[result.length] = cj;
                    }
                }
            }
        }
        return result;
    };
    
    function concat(a, b){
        if(b.slice){
            return a.concat(b);
        }
        for(var i = 0, l = b.length; i < l; i++){
            a[a.length] = b[i];
        }
        return a;
    }
    
    function byTag(cs, tagName){
        if(cs.tagName || cs == document){
            cs = [cs];
        }
        if(!tagName){
            return cs;
        }
        var r = []; tagName = tagName.toLowerCase();
        for(var i = 0, ci; ci = cs[i]; i++){
            if(ci.nodeType == 1 && ci.tagName.toLowerCase()==tagName){
                r[r.length] = ci;
            }
        }
        return r; 
    };
    
    function byId(cs, attr, id){
        if(cs.tagName || cs == document){
            cs = [cs];
        }
        if(!id){
            return cs;
        }
        var r = [];
        for(var i = 0,ci; ci = cs[i]; i++){
            if(ci && ci.id == id){
                r[r.length] = ci;
                return r;
            }
        }
        return r; 
    };
    
    function byAttribute(cs, attr, value, op, custom){
        var r = [], st = custom=="{";
        var f = Ext.DomQuery.operators[op];
        for(var i = 0; ci = cs[i]; i++){
            var a;
            if(st){
                a = Ext.DomQuery.getStyle(ci, attr);
            }
            else if(attr == "class" || attr == "className"){
                a = ci.className;
            }else if(attr == "for"){
                a = ci.htmlFor;
            }else if(attr == "href"){
                a = ci.getAttribute("href", 2);
            }else{
                a = ci.getAttribute(attr);
            }
            if((f && f(a, value)) || (!f && a)){
                r[r.length] = ci;
            }
        }
        return r;
    };
    
    function byPseudo(cs, name, value){
        return Ext.DomQuery.pseudos[name](cs, value);
    };
    
    // This is for IE MSXML which does not support expandos.
    // IE runs the same speed using setAttribute, however FF slows way down
    // and Safari completely fails so they need to continue to use expandos.
    var isIE = window.ActiveXObject ? true : false;

    var key = 30803;

    function nodupIEXml(cs){
        var d = ++key;
        cs[0].setAttribute("_nodup", d);
        var r = [cs[0]];
        for(var i = 1, len = cs.length; i < len; i++){
            var c = cs[i];
            if(!c.getAttribute("_nodup") != d){
                c.setAttribute("_nodup", d);
                r[r.length] = c;
            }
        }
        for(var i = 0, len = cs.length; i < len; i++){
            cs[i].removeAttribute("_nodup");
        }
        return r;
    }

    function nodup(cs){
        if(!cs){
            return [];
        }
        var len = cs.length, c, i, r = cs, cj;
        if(!len || typeof cs.nodeType != "undefined" || len == 1){
            return cs;
        }
        if(isIE && typeof cs[0].selectSingleNode != "undefined"){
            return nodupIEXml(cs);
        }
        var d = ++key;
        cs[0]._nodup = d;
        for(i = 1; c = cs[i]; i++){
            if(c._nodup != d){
                c._nodup = d;
            }else{
                r = [];
                for(var j = 0; j < i; j++){
                    r[r.length] = cs[j];
                }
                for(j = i+1; cj = cs[j]; j++){
                    if(cj._nodup != d){
                        cj._nodup = d;
                        r[r.length] = cj;
                    }
                }
                return r;
            }
        }
        return r;
    }

    function quickDiffIEXml(c1, c2){
        var d = ++key;
        for(var i = 0, len = c1.length; i < len; i++){
            c1[i].setAttribute("_qdiff", d);
        }
        var r = [];
        for(var i = 0, len = c2.length; i < len; i++){
            if(c2[i].getAttribute("_qdiff") != d){
                r[r.length] = c2[i];
            }
        }
        for(var i = 0, len = c1.length; i < len; i++){
           c1[i].removeAttribute("_qdiff");
        }
        return r;
    }

    function quickDiff(c1, c2){
        var len1 = c1.length;
        if(!len1){
            return c2;
        }
        if(isIE && c1[0].selectSingleNode){
            return quickDiffIEXml(c1, c2);
        }
        var d = ++key;
        for(var i = 0; i < len1; i++){
            c1[i]._qdiff = d;
        }
        var r = [];
        for(var i = 0, len = c2.length; i < len; i++){
            if(c2[i]._qdiff != d){
                r[r.length] = c2[i];
            }
        }
        return r;
    }
    
    function quickId(ns, mode, root, id){
        if(ns == root){
           var d = root.ownerDocument || root;
           return d.getElementById(id);
        }
        ns = getNodes(ns, mode, "*");
        return byId(ns, null, id);
    }
    
    return {
        getStyle : function(el, name){
            return Ext.fly(el).getStyle(name);
        },
        
        compile : function(path, type){
            type = type || "select";
            
            var fn = ["var f = function(root){\n var mode; var n = root || document;\n"];
            var q = path, mode, lq;
            var tk = Ext.DomQuery.matchers;
            var tklen = tk.length;
            var mm;

            // accept leading mode switch
            var lmode = q.match(modeRe);
            if(lmode && lmode[1]){
                fn[fn.length] = 'mode="'+lmode[1]+'";';
                q = q.replace(lmode[1], "");
            }
            // strip leading slashes
            while(path.substr(0, 1)=="/"){
                path = path.substr(1);
            }
                        
            while(q && lq != q){
                lq = q;
                var tm = q.match(tagTokenRe);
                if(type == "select"){
                    if(tm){
                        if(tm[1] == "#"){
                            fn[fn.length] = 'n = quickId(n, mode, root, "'+tm[2]+'");';
                        }else{
                            fn[fn.length] = 'n = getNodes(n, mode, "'+tm[2]+'");';
                        }
                        q = q.replace(tm[0], "");
                    }else if(q.substr(0, 1) != '@'){
                        fn[fn.length] = 'n = getNodes(n, mode, "*");';
                    }
                }else{
                    if(tm){
                        if(tm[1] == "#"){
                            fn[fn.length] = 'n = byId(n, null, "'+tm[2]+'");';
                        }else{
                            fn[fn.length] = 'n = byTag(n, "'+tm[2]+'");';
                        }
                        q = q.replace(tm[0], "");
                    }
                }
                while(!(mm = q.match(modeRe))){
                    var matched = false;
                    for(var j = 0; j < tklen; j++){
                        var t = tk[j];
                        var m = q.match(t.re);
                        if(m){
                            fn[fn.length] = t.select.replace(tplRe, function(x, i){
                                                    return m[i];
                                                });
                            q = q.replace(m[0], "");
                            matched = true;
                            break;
                        }
                    }
                    // prevent infinite loop on bad selector
                    if(!matched){
                        throw 'Error parsing selector, parsing failed at "' + q + '"';
                    }
                }
                if(mm[1]){
                    fn[fn.length] = 'mode="'+mm[1]+'";';
                    q = q.replace(mm[1], "");
                }
            }
            fn[fn.length] = "return nodup(n);\n}";
            eval(fn.join(""));
            return f;
        },
        
        
        select : function(path, root, type){
            if(!root || root == document){
                root = document;
            }
            if(typeof root == "string"){
                root = document.getElementById(root);
            }
            var paths = path.split(",");
            var results = [];
            for(var i = 0, len = paths.length; i < len; i++){
                var p = paths[i].replace(trimRe, "");
                if(!cache[p]){
                    cache[p] = Ext.DomQuery.compile(p);
                    if(!cache[p]){
                        throw p + " is not a valid selector";
                    }
                }
                var result = cache[p](root);
                if(result && result != document){
                    results = results.concat(result);
                }
            }
            return results;
        },
        
        
        selectNode : function(path, root){
            return Ext.DomQuery.select(path, root)[0];
        },
        
        
        selectValue : function(path, root, defaultValue){
            path = path.replace(trimRe, "");
            if(!valueCache[path]){
                valueCache[path] = Ext.DomQuery.compile(path, "select");
            }
            var n = valueCache[path](root);
            n = n[0] ? n[0] : n;
            var v = (n && n.firstChild ? n.firstChild.nodeValue : null);
            return (v === null ? defaultValue : v);
        },
        
        
        selectNumber : function(path, root, defaultValue){
            var v = Ext.DomQuery.selectValue(path, root, defaultValue || 0);
            return parseFloat(v);
        },
        
        
        is : function(el, ss){
            if(typeof el == "string"){
                el = document.getElementById(el);
            }
            var isArray = (el instanceof Array);
            var result = Ext.DomQuery.filter(isArray ? el : [el], ss);
            return isArray ? (result.length == el.length) : (result.length > 0);
        },
        
        
        filter : function(els, ss, nonMatches){
            ss = ss.replace(trimRe, "");
            if(!simpleCache[ss]){
                simpleCache[ss] = Ext.DomQuery.compile(ss, "simple");
            }
            var result = simpleCache[ss](els);
            return nonMatches ? quickDiff(result, els) : result;
        },
        
        
        matchers : [{
                re: /^\.([\w-]+)/,
                select: 'n = byClassName(n, null, " {1} ");'
            }, {
                re: /^\:([\w-]+)(?:\(((?:[^\s>\/]*|.*?))\))?/,
                select: 'n = byPseudo(n, "{1}", "{2}");'
            },{
                re: /^(?:([\[\{])(?:@)?([\w-]+)\s?(?:(=|.=)\s?['"]?(.*?)["']?)?[\]\}])/,
                select: 'n = byAttribute(n, "{2}", "{4}", "{3}", "{1}");'
            }, {
                re: /^#([\w-]+)/,
                select: 'n = byId(n, null, "{1}");'
            },{
                re: /^@([\w-]+)/,
                select: 'return {firstChild:{nodeValue:attrValue(n, "{1}")}};'
            }
        ],
        
        
        operators : {
            "=" : function(a, v){
                return a == v;
            },
            "!=" : function(a, v){
                return a != v;
            },
            "^=" : function(a, v){
                return a && a.substr(0, v.length) == v;
            },
            "$=" : function(a, v){
                return a && a.substr(a.length-v.length) == v;
            },
            "*=" : function(a, v){
                return a && a.indexOf(v) !== -1;
            },
            "%=" : function(a, v){
                return (a % v) == 0;
            }
        },
        
        
        pseudos : {
            "first-child" : function(c){
                var r = [], n;
                for(var i = 0, ci; ci = n = c[i]; i++){
                    while((n = n.previousSibling) && n.nodeType != 1);
                    if(!n){
                        r[r.length] = ci;
                    }
                }
                return r;
            },
            
            "last-child" : function(c){
                var r = [];
                for(var i = 0, ci; ci = n = c[i]; i++){
                    while((n = n.nextSibling) && n.nodeType != 1);
                    if(!n){
                        r[r.length] = ci;
                    }
                }
                return r;
            },
            
            "nth-child" : function(c, a){
                var r = [];
                if(a != "odd" && a != "even"){
                    for(var i = 0, ci; ci = c[i]; i++){
                        var m = child(ci.parentNode, a);
                        if(m == ci){
                            r[r.length] = m;
                        }
                    }
                    return r;
                }
                var p;
                // first let's clean up the parent nodes
                for(var i = 0, l = c.length; i < l; i++){
                    var cp = c[i].parentNode;
                    if(cp != p){
                        clean(cp);
                        p = cp;
                    }
                }
                // then lets see if we match
                for(var i = 0, ci; ci = c[i]; i++){
                    var m = false;
                    if(a == "odd"){
                        m = ((ci.nodeIndex+1) % 2 == 1);
                    }else if(a == "even"){
                        m = ((ci.nodeIndex+1) % 2 == 0);
                    }
                    if(m){
                        r[r.length] = ci;
                    }
                }
                return r;
            },
            
            "only-child" : function(c){
                var r = [];
                for(var i = 0, ci; ci = c[i]; i++){
                    if(!prev(ci) && !next(ci)){
                        r[r.length] = ci;
                    }
                }
                return r;
            },
            
            "empty" : function(c){
                var r = [];
                for(var i = 0, ci; ci = c[i]; i++){
                    var cns = ci.childNodes, j = 0, cn, empty = true;
                    while(cn = cns[j]){
                        ++j;
                        if(cn.nodeType == 1 || cn.nodeType == 3){
                            empty = false;
                            break;
                        }
                    }
                    if(empty){
                        r[r.length] = ci;
                    }
                }
                return r;
            },
            
            "contains" : function(c, v){
                var r = [];
                for(var i = 0, ci; ci = c[i]; i++){
                    if(ci.innerHTML.indexOf(v) !== -1){
                        r[r.length] = ci;
                    }
                }
                return r;
            },

            "nodeValue" : function(c, v){
                var r = [];
                for(var i = 0, ci; ci = c[i]; i++){
                    if(ci.firstChild && ci.firstChild.nodeValue == v){
                        r[r.length] = ci;
                    }
                }
                return r;
            },

            "checked" : function(c){
                var r = [];
                for(var i = 0, ci; ci = c[i]; i++){
                    if(ci.checked == true){
                        r[r.length] = ci;
                    }
                }
                return r;
            },
            
            "not" : function(c, ss){
                return Ext.DomQuery.filter(c, ss, true);
            },
            
            "odd" : function(c){
                return this["nth-child"](c, "odd");
            },
            
            "even" : function(c){
                return this["nth-child"](c, "even");
            },
            
            "nth" : function(c, a){
                return c[a-1] || [];
            },
            
            "first" : function(c){
                return c[0] || [];
            },
            
            "last" : function(c){
                return c[c.length-1] || [];
            },
            
            "has" : function(c, ss){
                var s = Ext.DomQuery.select;
                var r = [];
                for(var i = 0, ci; ci = c[i]; i++){
                    if(s(ss, ci).length > 0){
                        r[r.length] = ci;
                    }
                }
                return r;
            },
            
            "next" : function(c, ss){
                var is = Ext.DomQuery.is;
                var r = [];
                for(var i = 0, ci; ci = c[i]; i++){
                    var n = next(ci);
                    if(n && is(n, ss)){
                        r[r.length] = ci;
                    }
                }
                return r;
            },
            
            "prev" : function(c, ss){
                var is = Ext.DomQuery.is;
                var r = [];
                for(var i = 0, ci; ci = c[i]; i++){
                    var n = prev(ci);
                    if(n && is(n, ss)){
                        r[r.length] = ci;
                    }
                }
                return r;
            }
        }
    };
}();


Ext.query = Ext.DomQuery.select;


Ext.util.Observable = function(){
    if(this.listeners){
        this.on(this.listeners);
        delete this.listeners;
    }
};
Ext.util.Observable.prototype = {
    
    fireEvent : function(){
        var ce = this.events[arguments[0].toLowerCase()];
        if(typeof ce == "object"){
            return ce.fire.apply(ce, Array.prototype.slice.call(arguments, 1));
        }else{
            return true;
        }
    },

    // private
    filterOptRe : /^(?:scope|delay|buffer|single)$/,

    
    addListener : function(eventName, fn, scope, o){
        if(typeof eventName == "object"){
            o = eventName;
            for(var e in o){
                if(this.filterOptRe.test(e)){
                    continue;
                }
                if(typeof o[e] == "function"){
                    // shared options
                    this.addListener(e, o[e], o.scope,  o);
                }else{
                    // individual options
                    this.addListener(e, o[e].fn, o[e].scope, o[e]);
                }
            }
            return;
        }
        o = (!o || typeof o == "boolean") ? {} : o;
        eventName = eventName.toLowerCase();
        var ce = this.events[eventName] || true;
        if(typeof ce == "boolean"){
            ce = new Ext.util.Event(this, eventName);
            this.events[eventName] = ce;
        }
        ce.addListener(fn, scope, o);
    },

    
    removeListener : function(eventName, fn, scope){
        var ce = this.events[eventName.toLowerCase()];
        if(typeof ce == "object"){
            ce.removeListener(fn, scope);
        }
    },

    
    purgeListeners : function(){
        for(var evt in this.events){
            if(typeof this.events[evt] == "object"){
                 this.events[evt].clearListeners();
            }
        }
    },

    relayEvents : function(o, events){
        var createHandler = function(ename){
            return function(){
                return this.fireEvent.apply(this, Ext.combine(ename, Array.prototype.slice.call(arguments, 0)));
            };
        };
        for(var i = 0, len = events.length; i < len; i++){
            var ename = events[i];
            if(!this.events[ename]){ this.events[ename] = true; };
            o.on(ename, createHandler(ename), this);
        }
    },

    
    addEvents : function(o){
        if(!this.events){
            this.events = {};
        }
        Ext.applyIf(this.events, o);
    },

    
    hasListener : function(eventName){
        var e = this.events[eventName];
        return typeof e == "object" && e.listeners.length > 0;
    }
};

Ext.util.Observable.prototype.on = Ext.util.Observable.prototype.addListener;

Ext.util.Observable.prototype.un = Ext.util.Observable.prototype.removeListener;


Ext.util.Observable.capture = function(o, fn, scope){
    o.fireEvent = o.fireEvent.createInterceptor(fn, scope);
};


Ext.util.Observable.releaseCapture = function(o){
    o.fireEvent = Ext.util.Observable.prototype.fireEvent;
};

(function(){

    var createBuffered = function(h, o, scope){
        var task = new Ext.util.DelayedTask();
        return function(){
            task.delay(o.buffer, h, scope, Array.prototype.slice.call(arguments, 0));
        };
    };

    var createSingle = function(h, e, fn, scope){
        return function(){
            e.removeListener(fn, scope);
            return h.apply(scope, arguments);
        };
    };

    var createDelayed = function(h, o, scope){
        return function(){
            var args = Array.prototype.slice.call(arguments, 0);
            setTimeout(function(){
                h.apply(scope, args);
            }, o.delay || 10);
        };
    };

    Ext.util.Event = function(obj, name){
        this.name = name;
        this.obj = obj;
        this.listeners = [];
    };

    Ext.util.Event.prototype = {
        addListener : function(fn, scope, options){
            var o = options || {};
            scope = scope || this.obj;
            if(!this.isListening(fn, scope)){
                var l = {fn: fn, scope: scope, options: o};
                var h = fn;
                if(o.delay){
                    h = createDelayed(h, o, scope);
                }
                if(o.single){
                    h = createSingle(h, this, fn, scope);
                }
                if(o.buffer){
                    h = createBuffered(h, o, scope);
                }
                l.fireFn = h;
                if(!this.firing){ // if we are currently firing this event, don't disturb the listener loop
                    this.listeners.push(l);
                }else{
                    this.listeners = this.listeners.slice(0);
                    this.listeners.push(l);
                }
            }
        },

        findListener : function(fn, scope){
            scope = scope || this.obj;
            var ls = this.listeners;
            for(var i = 0, len = ls.length; i < len; i++){
                var l = ls[i];
                if(l.fn == fn && l.scope == scope){
                    return i;
                }
            }
            return -1;
        },

        isListening : function(fn, scope){
            return this.findListener(fn, scope) != -1;
        },

        removeListener : function(fn, scope){
            var index;
            if((index = this.findListener(fn, scope)) != -1){
                if(!this.firing){
                    this.listeners.splice(index, 1);
                }else{
                    this.listeners = this.listeners.slice(0);
                    this.listeners.splice(index, 1);
                }
                return true;
            }
            return false;
        },

        clearListeners : function(){
            this.listeners = [];
        },

        fire : function(){
            var ls = this.listeners, scope, len = ls.length;
            if(len > 0){
                this.firing = true;
                var args = Array.prototype.slice.call(arguments, 0);
                for(var i = 0; i < len; i++){
                    var l = ls[i];
                    if(l.fireFn.apply(l.scope, arguments) === false){
                        this.firing = false;
                        return false;
                    }
                }
                this.firing = false;
            }
            return true;
        }
    };
})();

Ext.EventManager = function(){
    var docReadyEvent, docReadyProcId, docReadyState = false;
    var resizeEvent, resizeTask, textEvent, textSize;
    var E = Ext.lib.Event;
    var D = Ext.lib.Dom;


    var fireDocReady = function(){
        if(!docReadyState){
            docReadyState = true;
            Ext.isReady = true;
            if(docReadyProcId){
                clearInterval(docReadyProcId);
            }
            if(Ext.isGecko || Ext.isOpera) {
                document.removeEventListener("DOMContentLoaded", fireDocReady, false);
            }
            if(docReadyEvent){
                docReadyEvent.fire();
                docReadyEvent.clearListeners();
            }
        }
    };
    
    var initDocReady = function(){
        docReadyEvent = new Ext.util.Event();
        if(Ext.isGecko || Ext.isOpera) {
            document.addEventListener("DOMContentLoaded", fireDocReady, false);
        }else if(Ext.isIE){
            // inspired by  http://www.thefutureoftheweb.com/blog/2006/6/adddomloadevent
            document.write("<s"+'cript id="ie-deferred-loader" defer="defer" src="/'+'/:"></s'+"cript>");
            var defer = document.getElementById("ie-deferred-loader");
            defer.onreadystatechange = function(){
                if(this.readyState == "complete"){
                    fireDocReady();
                    defer.onreadystatechange = null;
                    defer.parentNode.removeChild(defer);
                }
            };
        }else if(Ext.isSafari){ 
            docReadyProcId = setInterval(function(){
                var rs = document.readyState;
                if(rs == "complete") {
                    fireDocReady();     
                 }
            }, 10);
        }
        // no matter what, make sure it fires on load
        E.on(window, "load", fireDocReady);
    };

    var createBuffered = function(h, o){
        var task = new Ext.util.DelayedTask(h);
        return function(e){
            // create new event object impl so new events don't wipe out properties
            e = new Ext.EventObjectImpl(e);
            task.delay(o.buffer, h, null, [e]);
        };
    };

    var createSingle = function(h, el, ename, fn){
        return function(e){
            Ext.EventManager.removeListener(el, ename, fn);
            h(e);
        };
    };

    var createDelayed = function(h, o){
        return function(e){
            // create new event object impl so new events don't wipe out properties
            e = new Ext.EventObjectImpl(e);
            setTimeout(function(){
                h(e);
            }, o.delay || 10);
        };
    };

    var listen = function(element, ename, opt, fn, scope){
        var o = (!opt || typeof opt == "boolean") ? {} : opt;
        fn = fn || o.fn; scope = scope || o.scope;
        var el = Ext.getDom(element);
        if(!el){
            throw "Error listening for \"" + ename + '\". Element "' + element + '" doesn\'t exist.';
        }
        var h = function(e){
            e = Ext.EventObject.setEvent(e);
            var t;
            if(o.delegate){
                t = e.getTarget(o.delegate, el);
                if(!t){
                    return;
                }
            }else{
                t = e.target;
            }
            if(o.stopEvent === true){
                e.stopEvent();
            }
            if(o.preventDefault === true){
               e.preventDefault();
            }
            if(o.stopPropagation === true){
                e.stopPropagation();
            }

            if(o.normalized === false){
                e = e.browserEvent;
            }

            fn.call(scope || el, e, t, o);
        };
        if(o.delay){
            h = createDelayed(h, o);
        }
        if(o.single){
            h = createSingle(h, el, ename, fn);
        }
        if(o.buffer){
            h = createBuffered(h, o);
        }
        fn._handlers = fn._handlers || [];
        fn._handlers.push([Ext.id(el), ename, h]);

        E.on(el, ename, h);
        if(ename == "mousewheel" && el.addEventListener){ // workaround for jQuery
            el.addEventListener("DOMMouseScroll", h, false);
            E.on(window, 'unload', function(){
                el.removeEventListener("DOMMouseScroll", h, false);
            });
        }
        if(ename == "mousedown" && el == document){ // fix stopped mousedowns on the document
            Ext.EventManager.stoppedMouseDownEvent.addListener(h);
        }
        return h;
    };

    var stopListening = function(el, ename, fn){
        var id = Ext.id(el), hds = fn._handlers, hd = fn;
        if(hds){
            for(var i = 0, len = hds.length; i < len; i++){
                var h = hds[i];
                if(h[0] == id && h[1] == ename){
                    hd = h[2];
                    hds.splice(i, 1);
                    break;
                }
            }
        }
        E.un(el, ename, hd);
        el = Ext.getDom(el);
        if(ename == "mousewheel" && el.addEventListener){
            el.removeEventListener("DOMMouseScroll", hd, false);
        }
        if(ename == "mousedown" && el == document){ // fix stopped mousedowns on the document
            Ext.EventManager.stoppedMouseDownEvent.removeListener(hd);
        }
    };

    var propRe = /^(?:scope|delay|buffer|single|stopEvent|preventDefault|stopPropagation|normalized)$/;
    var pub = {
        
        
        wrap : function(fn, scope, override){
            return function(e){
                Ext.EventObject.setEvent(e);
                fn.call(override ? scope || window : window, Ext.EventObject, scope);
            };
        },
        
        
        addListener : function(element, eventName, fn, scope, options){
            if(typeof eventName == "object"){
                var o = eventName;
                for(var e in o){
                    if(propRe.test(e)){
                        continue;
                    }
                    if(typeof o[e] == "function"){
                        // shared options
                        listen(element, e, o, o[e], o.scope);
                    }else{
                        // individual options
                        listen(element, e, o[e]);
                    }
                }
                return;
            }
            return listen(element, eventName, options, fn, scope);
        },
        
        
        removeListener : function(element, eventName, fn){
            return stopListening(element, eventName, fn);
        },
        
        
        onDocumentReady : function(fn, scope, options){
            if(docReadyState){ // if it already fired
                fn.call(scope || window, scope);
                return;
            }
            if(!docReadyEvent){
                initDocReady();
            }
            docReadyEvent.addListener(fn, scope, options);
        },
        
        
        onWindowResize : function(fn, scope, options){
            if(!resizeEvent){
                resizeEvent = new Ext.util.Event();
                resizeTask = new Ext.util.DelayedTask(function(){
                    resizeEvent.fire(D.getViewWidth(), D.getViewHeight());
                });
                E.on(window, "resize", function(){
                    if(Ext.isIE){
                        resizeTask.delay(50);
                    }else{
                        resizeEvent.fire(D.getViewWidth(), D.getViewHeight());
                    }
                });
            }
            resizeEvent.addListener(fn, scope, options);
        },

        
        onTextResize : function(fn, scope, options){
            if(!textEvent){
                textEvent = new Ext.util.Event();
                var textEl = new Ext.Element(document.createElement('div'));
                textEl.dom.className = 'x-text-resize';
                textEl.dom.innerHTML = 'X';
                textEl.appendTo(document.body);
                textSize = textEl.dom.offsetHeight;
                setInterval(function(){
                    if(textEl.dom.offsetHeight != textSize){
                        textEvent.fire(textSize, textSize = textEl.dom.offsetHeight);
                    }
                }, this.textResizeInterval);
            }
            textEvent.addListener(fn, scope, options);
        },

        
        removeResizeListener : function(fn, scope){
            if(resizeEvent){
                resizeEvent.removeListener(fn, scope);
            }
        },
        
        fireResize : function(){
            if(resizeEvent){
                resizeEvent.fire(D.getViewWidth(), D.getViewHeight());
            }   
        },
        
        ieDeferSrc : false,
        textResizeInterval : 50
    };
    
    pub.on = pub.addListener;
    pub.un = pub.removeListener;

    pub.stoppedMouseDownEvent = new Ext.util.Event();
    return pub;
}();

Ext.onReady = Ext.EventManager.onDocumentReady;

Ext.onReady(function(){
    var bd = Ext.get(document.body);
    if(!bd){ return; }
    var cls = Ext.isIE ? "ext-ie"
            : Ext.isGecko ? "ext-gecko"
            : Ext.isOpera ? "ext-opera"
            : Ext.isSafari ? "ext-safari" : "";
    if(Ext.isBorderBox){
        cls += ' ext-border-box';
    }
    if(Ext.isStrict){
        cls += ' ext-strict';
    }
    bd.addClass(cls);
});

Ext.EventObject = function(){
    
    var E = Ext.lib.Event;
    
    // safari keypress events for special keys return bad keycodes
    var safariKeys = {
        63234 : 37, // left
        63235 : 39, // right
        63232 : 38, // up
        63233 : 40, // down
        63276 : 33, // page up
        63277 : 34, // page down
        63272 : 46, // delete
        63273 : 36, // home
        63275 : 35  // end
    };

    // normalize button clicks
    var btnMap = Ext.isIE ? {1:0,4:1,2:2} :
                (Ext.isSafari ? {1:0,2:1,3:2} : {0:0,1:1,2:2});

    Ext.EventObjectImpl = function(e){
        if(e){
            this.setEvent(e.browserEvent || e);
        }
    };
    Ext.EventObjectImpl.prototype = {
        
        browserEvent : null,
        
        button : -1,
        
        shiftKey : false,
        
        ctrlKey : false,
        
        altKey : false,

        
        BACKSPACE : 8,
        
        TAB : 9,
        
        RETURN : 13,
        
        ENTER : 13,
        
        SHIFT : 16,
        
        CONTROL : 17,
        
        ESC : 27,
        
        SPACE : 32,
        
        PAGEUP : 33,
        
        PAGEDOWN : 34,
        
        END : 35,
        
        HOME : 36,
        
        LEFT : 37,
        
        UP : 38,
        
        RIGHT : 39,
        
        DOWN : 40,
        
        DELETE : 46,
        
        F5 : 116,

           
        setEvent : function(e){
            if(e == this || (e && e.browserEvent)){ // already wrapped
                return e;
            }
            this.browserEvent = e;
            if(e){
                // normalize buttons
                this.button = e.button ? btnMap[e.button] : (e.which ? e.which-1 : -1);
                if(e.type == 'click' && this.button == -1){
                    this.button = 0;
                }
                this.shiftKey = e.shiftKey;
                // mac metaKey behaves like ctrlKey
                this.ctrlKey = e.ctrlKey || e.metaKey;
                this.altKey = e.altKey;
                // in getKey these will be normalized for the mac
                this.keyCode = e.keyCode;
                this.charCode = e.charCode;
                // cache the target for the delayed and or buffered events
                this.target = E.getTarget(e);
                // same for XY
                this.xy = E.getXY(e);
            }else{
                this.button = -1;
                this.shiftKey = false;
                this.ctrlKey = false;
                this.altKey = false;
                this.keyCode = 0;
                this.charCode =0;
                this.target = null;
                this.xy = [0, 0];
            }
            return this;
        },

        
        stopEvent : function(){
            if(this.browserEvent){
                if(this.browserEvent.type == 'mousedown'){
                    Ext.EventManager.stoppedMouseDownEvent.fire(this);
                }
                E.stopEvent(this.browserEvent);
            }
        },

        
        preventDefault : function(){
            if(this.browserEvent){
                E.preventDefault(this.browserEvent);
            }
        },

        
        isNavKeyPress : function(){
            var k = this.keyCode;
            k = Ext.isSafari ? (safariKeys[k] || k) : k;
            return (k >= 33 && k <= 40) || k == this.RETURN || k == this.TAB || k == this.ESC;
        },

        isSpecialKey : function(){
            var k = this.keyCode;
            return k == 9 || k == 13  || k == 40 || k == 27 ||
            (k == 16) || (k == 17) ||
            (k >= 18 && k <= 20) ||
            (k >= 33 && k <= 35) ||
            (k >= 36 && k <= 39) ||
            (k >= 44 && k <= 45);
        },
        
        stopPropagation : function(){
            if(this.browserEvent){
                if(this.browserEvent.type == 'mousedown'){
                    Ext.EventManager.stoppedMouseDownEvent.fire(this);
                }
                E.stopPropagation(this.browserEvent);
            }
        },

        
        getCharCode : function(){
            return this.charCode || this.keyCode;
        },

        
        getKey : function(){
            var k = this.keyCode || this.charCode;
            return Ext.isSafari ? (safariKeys[k] || k) : k;
        },

        
        getPageX : function(){
            return this.xy[0];
        },

        
        getPageY : function(){
            return this.xy[1];
        },

        
        getTime : function(){
            if(this.browserEvent){
                return E.getTime(this.browserEvent);
            }
            return null;
        },

        
        getXY : function(){
            return this.xy;
        },

        
        getTarget : function(selector, maxDepth, returnEl){
            return selector ? Ext.fly(this.target).findParent(selector, maxDepth, returnEl) : this.target;
        },
        
        getRelatedTarget : function(){
            if(this.browserEvent){
                return E.getRelatedTarget(this.browserEvent);
            }
            return null;
        },

        
        getWheelDelta : function(){
            var e = this.browserEvent;
            var delta = 0;
            if(e.wheelDelta){ 
                delta = e.wheelDelta/120;
                
                if(window.opera) delta = -delta;
            }else if(e.detail){ 
                delta = -e.detail/3;
            }
            return delta;
        },

        
        hasModifier : function(){
            return ((this.ctrlKey || this.altKey) || this.shiftKey) ? true : false;
        },

        
        within : function(el, related){
            var t = this[related ? "getRelatedTarget" : "getTarget"]();
            return t && Ext.fly(el).contains(t);
        },

        getPoint : function(){
            return new Ext.lib.Point(this.xy[0], this.xy[1]);
        }
    };

    return new Ext.EventObjectImpl();
}();
            
    

(function(){
var D = Ext.lib.Dom;
var E = Ext.lib.Event;
var A = Ext.lib.Anim;
    
// local style camelizing for speed
var propCache = {};
var camelRe = /(-[a-z])/gi;
var camelFn = function(m, a){ return a.charAt(1).toUpperCase(); };
var view = document.defaultView;

Ext.Element = function(element, forceNew){
    var dom = typeof element == "string" ?
            document.getElementById(element) : element;
    if(!dom){ // invalid id/element
        return null;
    }
    if(!forceNew && Ext.Element.cache[dom.id]){ // element object already exists
        return Ext.Element.cache[dom.id];
    }
    
    this.dom = dom;
    
    
    this.id = dom.id || Ext.id(dom);
};

var El = Ext.Element;

El.prototype = {
    
    originalDisplay : "",
    
    visibilityMode : 1,
    
    defaultUnit : "px",
    
    setVisibilityMode : function(visMode){
        this.visibilityMode = visMode;
        return this;
    },
    
    enableDisplayMode : function(display){
        this.setVisibilityMode(El.DISPLAY);
        if(typeof display != "undefined") this.originalDisplay = display;
        return this;
    },
    
    
    findParent : function(simpleSelector, maxDepth, returnEl){
        var p = this.dom, b = document.body, depth = 0, dq = Ext.DomQuery, stopEl;
        maxDepth = maxDepth || 50;
        if(typeof maxDepth != "number"){
            stopEl = Ext.getDom(maxDepth);
            maxDepth = 10;
        }
        while(p && p.nodeType == 1 && depth < maxDepth && p != b && p != stopEl){
            if(dq.is(p, simpleSelector)){
                return returnEl ? Ext.get(p) : p;
            }
            depth++;
            p = p.parentNode;
        }
        return null;
    },


    
    findParentNode : function(simpleSelector, maxDepth, returnEl){
        var p = Ext.fly(this.dom.parentNode, '_internal');
        return p ? p.findParent(simpleSelector, maxDepth, returnEl) : null;
    },

    
    up : function(simpleSelector, maxDepth){
        return this.findParentNode(simpleSelector, maxDepth, true);
    },



    
    is : function(simpleSelector){
        return Ext.DomQuery.is(this.dom, simpleSelector);    
    },
    
    
    animate : function(args, duration, onComplete, easing, animType){
        this.anim(args, {duration: duration, callback: onComplete, easing: easing}, animType);
        return this;
    },
    
    
    anim : function(args, opt, animType, defaultDur, defaultEase, cb){
        animType = animType || 'run';
        opt = opt || {};
        var anim = Ext.lib.Anim[animType](
            this.dom, args,
            (opt.duration || defaultDur) || .35,
            (opt.easing || defaultEase) || 'easeOut',
            function(){
                Ext.callback(cb, this);
                Ext.callback(opt.callback, opt.scope || this, [this, opt]);
            },
            this
        );
        opt.anim = anim;
        return anim;
    },
    
    // private legacy anim prep
    preanim : function(a, i){
        return !a[i] ? false : (typeof a[i] == "object" ? a[i]: {duration: a[i+1], callback: a[i+2], easing: a[i+3]});
    },
    
    
    clean : function(forceReclean){
        if(this.isCleaned && forceReclean !== true){
            return this;
        }
        var ns = /\S/;
        var d = this.dom, n = d.firstChild, ni = -1;
 	    while(n){
 	        var nx = n.nextSibling;
 	        if(n.nodeType == 3 && !ns.test(n.nodeValue)){
 	            d.removeChild(n);
 	        }else{
 	            n.nodeIndex = ++ni;
 	        }
 	        n = nx;
 	    }
 	    this.isCleaned = true;
 	    return this;
 	},    

    // private
    calcOffsetsTo : function(el){
        el = Ext.get(el);
        var d = el.dom;
        var restorePos = false;
        if(el.getStyle('position') == 'static'){
            el.position('relative');
            restorePos = true;
        }
        var x = 0, y =0;
        var op = this.dom;
        while(op && op != d && op.tagName != 'HTML'){
            x+= op.offsetLeft;
            y+= op.offsetTop;
            op = op.offsetParent;
        }
        if(restorePos){
            el.position('static');
        }
        return [x, y];
    },

    
    scrollIntoView : function(container, hscroll){
        var c = Ext.getDom(container) || document.body;
        var el = this.dom;

        var o = this.calcOffsetsTo(c),
            l = o[0],
            t = o[1],
            b = t+el.offsetHeight,
            r = l+el.offsetWidth;
        
        var ch = c.clientHeight;
        var ct = parseInt(c.scrollTop, 10);
        var cl = parseInt(c.scrollLeft, 10);
        var cb = ct + ch;
        var cr = cl + c.clientWidth;

        if(t < ct){
        	c.scrollTop = t;
        }else if(b > cb){
            c.scrollTop = b-ch;
        }

        if(hscroll !== false){
            if(l < cl){
                c.scrollLeft = l;
            }else if(r > cr){
                c.scrollLeft = r-c.clientWidth;
            }
        }
        return this;
    },

    // private
    scrollChildIntoView : function(child, hscroll){
        Ext.fly(child, '_scrollChildIntoView').scrollIntoView(this, hscroll);
    },

    
    autoHeight : function(animate, duration, onComplete, easing){
        var oldHeight = this.getHeight();
        this.clip();
        this.setHeight(1); // force clipping
        setTimeout(function(){
            var height = parseInt(this.dom.scrollHeight, 10); // parseInt for Safari
            if(!animate){
                this.setHeight(height);
                this.unclip();
                if(typeof onComplete == "function"){
                    onComplete();
                }
            }else{
                this.setHeight(oldHeight); // restore original height
                this.setHeight(height, animate, duration, function(){
                    this.unclip();
                    if(typeof onComplete == "function") onComplete();
                }.createDelegate(this), easing);
            }
        }.createDelegate(this), 0);
        return this;
    },
    
    
    contains : function(el){
        if(!el){return false;}
        return D.isAncestor(this.dom, el.dom ? el.dom : el);
    },
    
    
    isVisible : function(deep) {
        var vis = !(this.getStyle("visibility") == "hidden" || this.getStyle("display") == "none");
        if(deep !== true || !vis){
            return vis;
        }
        var p = this.dom.parentNode;
        while(p && p.tagName.toLowerCase() != "body"){
            if(!Ext.fly(p, '_isVisible').isVisible()){
                return false;
            }
            p = p.parentNode;
        }
        return true;
    },
    
    
    select : function(selector, unique){
        return El.select(selector, unique, this.dom);
    },
    
    
    query : function(selector, unique){
        return Ext.DomQuery.select(selector, this.dom);
    },
    
    
    child : function(selector, returnDom){
        var n = Ext.DomQuery.selectNode(selector, this.dom);
        return returnDom ? n : Ext.get(n);
    },

    
    down : function(selector, returnDom){
        var n = Ext.DomQuery.selectNode(" > " + selector, this.dom);
        return returnDom ? n : Ext.get(n);
    },

    
    initDD : function(group, config, overrides){
        var dd = new Ext.dd.DD(Ext.id(this.dom), group, config);
        return Ext.apply(dd, overrides);
    },
   
    
    initDDProxy : function(group, config, overrides){
        var dd = new Ext.dd.DDProxy(Ext.id(this.dom), group, config);
        return Ext.apply(dd, overrides);
    },
   
    
    initDDTarget : function(group, config, overrides){
        var dd = new Ext.dd.DDTarget(Ext.id(this.dom), group, config);
        return Ext.apply(dd, overrides);
    },
   
    
     setVisible : function(visible, animate){
        if(!animate || !A){
            if(this.visibilityMode == El.DISPLAY){
                this.setDisplayed(visible);
            }else{
                this.fixDisplay();
                this.dom.style.visibility = visible ? "visible" : "hidden";
            }
        }else{
            // closure for composites
            var dom = this.dom;
            var visMode = this.visibilityMode;
            if(visible){
                this.setOpacity(.01);
                this.setVisible(true);
            }
            this.anim({opacity: { to: (visible?1:0) }},
                  this.preanim(arguments, 1),
                  null, .35, 'easeIn', function(){
                     if(!visible){
                         if(visMode == El.DISPLAY){
                             dom.style.display = "none";
                         }else{
                             dom.style.visibility = "hidden";
                         }
                         Ext.get(dom).setOpacity(1);
                     }
                 });
        }
        return this;
    },

    
    isDisplayed : function() {
        return this.getStyle("display") != "none";
    },
    
    
    toggle : function(animate){
        this.setVisible(!this.isVisible(), this.preanim(arguments, 0));
        return this;
    },
    
    
    setDisplayed : function(value) {
        if(typeof value == "boolean"){
           value = value ? this.originalDisplay : "none";
        }
        this.setStyle("display", value);
        return this;
    },
    
    
    focus : function() {
        try{
            this.dom.focus();
        }catch(e){}
        return this;
    },
    
    
    blur : function() {
        try{
            this.dom.blur();
        }catch(e){}
        return this;
    },
    
    
    addClass : function(className){
        if(className instanceof Array){
            for(var i = 0, len = className.length; i < len; i++) {
            	this.addClass(className[i]);
            }
        }else{
            if(className && !this.hasClass(className)){
                this.dom.className = this.dom.className + " " + className;
            }
        }
        return this;
    },
    
    
    radioClass : function(className){
        var siblings = this.dom.parentNode.childNodes;
        for(var i = 0; i < siblings.length; i++) {
        	var s = siblings[i];
        	if(s.nodeType == 1){
        	    Ext.get(s).removeClass(className);
        	}
        }
        this.addClass(className);
        return this;
    },
    
    
    removeClass : function(className){
        if(!className || !this.dom.className){
            return this;
        }
        if(className instanceof Array){
            for(var i = 0, len = className.length; i < len; i++) {
            	this.removeClass(className[i]);
            }
        }else{
            if(this.hasClass(className)){
                var re = this.classReCache[className];
                if (!re) {
                   re = new RegExp('(?:^|\\s+)' + className + '(?:\\s+|$)', "g");
                   this.classReCache[className] = re;
                }
                this.dom.className =
                    this.dom.className.replace(re, " ");
            }
        }
        return this;
    },

    // private
    classReCache: {},

    
    toggleClass : function(className){
        if(this.hasClass(className)){
            this.removeClass(className);
        }else{
            this.addClass(className);
        }
        return this;
    },
    
    
    hasClass : function(className){
        return className && (' '+this.dom.className+' ').indexOf(' '+className+' ') != -1;
    },
    
    
    replaceClass : function(oldClassName, newClassName){
        this.removeClass(oldClassName);
        this.addClass(newClassName);
        return this;
    },

    
    getStyles : function(){
        var a = arguments, len = a.length, r = {};
        for(var i = 0; i < len; i++){
            r[a[i]] = this.getStyle(a[i]);
        }
        return r;
    },

    
    getStyle : function(){
        return view && view.getComputedStyle ?
            function(prop){
                var el = this.dom, v, cs, camel;
                if(prop == 'float'){
                    prop = "cssFloat";
                }
                if(v = el.style[prop]){
                    return v;
                }
                if(cs = view.getComputedStyle(el, "")){
                    if(!(camel = propCache[prop])){
                        camel = propCache[prop] = prop.replace(camelRe, camelFn);
                    }
                    return cs[camel];
                }
                return null;
            } : 
            function(prop){
                var el = this.dom, v, cs, camel;
                if(prop == 'opacity'){
                    if(typeof el.filter == 'string'){
                        var fv = parseFloat(el.filter.match(/alpha\(opacity=(.*)\)/i)[1]);
                        if(!isNaN(fv)){
                            return fv ? fv / 100 : 0;
                        }
                    }
                    return 1;
                }else if(prop == 'float'){
                    prop = "styleFloat";
                }
                if(!(camel = propCache[prop])){
                    camel = propCache[prop] = prop.replace(camelRe, camelFn);
                }
                if(v = el.style[camel]){
                    return v;
                }
                if(cs = el.currentStyle){
                    return cs[camel];
                }
                return null;
            };
    }(),
    
    
    setStyle : function(prop, value){
        if(typeof prop == "string"){
            var camel;
            if(!(camel = propCache[prop])){
                camel = propCache[prop] = prop.replace(camelRe, camelFn);
            }
            if(camel == 'opacity') {
                this.setOpacity(value);
            }else{
                this.dom.style[camel] = value;
            }
        }else{
            for(var style in prop){
                if(typeof prop[style] != "function"){
                   this.setStyle(style, prop[style]);
                }
            }
        }
        return this;
    },
    
    
    applyStyles : function(style){
        Ext.DomHelper.applyStyles(this.dom, style);
        return this;
    },
    
    
    getX : function(){
        return D.getX(this.dom);
    },
    
    
    getY : function(){
        return D.getY(this.dom);
    },
    
    
    getXY : function(){
        return D.getXY(this.dom);
    },
    
    
    setX : function(x, animate){
        if(!animate || !A){
            D.setX(this.dom, x);
        }else{
            this.setXY([x, this.getY()], this.preanim(arguments, 1));
        }
        return this;
    },
    
    
    setY : function(y, animate){
        if(!animate || !A){
            D.setY(this.dom, y);
        }else{
            this.setXY([this.getX(), y], this.preanim(arguments, 1));
        }
        return this;
    },
    
    
    setLeft : function(left){
        this.setStyle("left", this.addUnits(left));
        return this;
    },
    
    
    setTop : function(top){
        this.setStyle("top", this.addUnits(top));
        return this;
    },
    
    
    setRight : function(right){
        this.setStyle("right", this.addUnits(right));
        return this;
    },
    
    
    setBottom : function(bottom){
        this.setStyle("bottom", this.addUnits(bottom));
        return this;
    },
    
    
    setXY : function(pos, animate){
        if(!animate || !A){
            D.setXY(this.dom, pos);
        }else{
            this.anim({points: {to: pos}}, this.preanim(arguments, 1), 'motion');
        }
        return this;
    },
    
    
    setLocation : function(x, y, animate){
        this.setXY([x, y], this.preanim(arguments, 2));
        return this;
    },
    
    
    moveTo : function(x, y, animate){
        this.setXY([x, y], this.preanim(arguments, 2));
        return this;
    },
    
    
    getRegion : function(){
        return D.getRegion(this.dom);
    },
    
    
    getHeight : function(contentHeight){
        var h = this.dom.offsetHeight || 0;
        return contentHeight !== true ? h : h-this.getBorderWidth("tb")-this.getPadding("tb");
    },

    
    getWidth : function(contentWidth){
        var w = this.dom.offsetWidth || 0;
        return contentWidth !== true ? w : w-this.getBorderWidth("lr")-this.getPadding("lr");
    },

    
    getComputedHeight : function(){
        var h = Math.max(this.dom.offsetHeight, this.dom.clientHeight);
        if(!h){
            h = parseInt(this.getStyle('height'), 10) || 0;
            if(!this.isBorderBox()){
                h += this.getFrameWidth('tb');
            }
        }
        return h;
    },

    
    getComputedWidth : function(){
        var w = Math.max(this.dom.offsetWidth, this.dom.clientWidth);
        if(!w){
            w = parseInt(this.getStyle('width'), 10) || 0;
            if(!this.isBorderBox()){
                w += this.getFrameWidth('lr');
            }
        }
        return w;
    },

    
    getSize : function(contentSize){
        return {width: this.getWidth(contentSize), height: this.getHeight(contentSize)};
    },

    
    getViewSize : function(){
        var d = this.dom, doc = document, aw = 0, ah = 0;
        if(d == doc || d == doc.body){
            return {width : D.getViewWidth(), height: D.getViewHeight()};
        }else{
            return {
                width : d.clientWidth,
                height: d.clientHeight
            };
        }
    },

    
    getValue : function(asNumber){
        return asNumber ? parseInt(this.dom.value, 10) : this.dom.value;
    },
    
    // private
    adjustWidth : function(width){
        if(typeof width == "number"){
            if(this.autoBoxAdjust && !this.isBorderBox()){
               width -= (this.getBorderWidth("lr") + this.getPadding("lr"));
            }
            if(width < 0){
                width = 0;
            }
        }
        return width;
    },
    
    // private
    adjustHeight : function(height){
        if(typeof height == "number"){
           if(this.autoBoxAdjust && !this.isBorderBox()){
               height -= (this.getBorderWidth("tb") + this.getPadding("tb"));
           }
           if(height < 0){
               height = 0;
           }
        }
        return height;
    },
    
    
    setWidth : function(width, animate){
        width = this.adjustWidth(width);
        if(!animate || !A){
            this.dom.style.width = this.addUnits(width); 
        }else{
            this.anim({width: {to: width}}, this.preanim(arguments, 1));
        }
        return this;
    },
    
    
     setHeight : function(height, animate){
        height = this.adjustHeight(height);
        if(!animate || !A){
            this.dom.style.height = this.addUnits(height);
        }else{
            this.anim({height: {to: height}}, this.preanim(arguments, 1));
        }
        return this;
    },
    
    
     setSize : function(width, height, animate){
        if(typeof width == "object"){ // in case of object from getSize()
            height = width.height; width = width.width;
        }
        width = this.adjustWidth(width); height = this.adjustHeight(height);
        if(!animate || !A){
            this.dom.style.width = this.addUnits(width);
            this.dom.style.height = this.addUnits(height);
        }else{
            this.anim({width: {to: width}, height: {to: height}}, this.preanim(arguments, 2));
        }
        return this;
    },

    
    setBounds : function(x, y, width, height, animate){
        if(!animate || !A){
            this.setSize(width, height);
            this.setLocation(x, y);
        }else{
            width = this.adjustWidth(width); height = this.adjustHeight(height);
            this.anim({points: {to: [x, y]}, width: {to: width}, height: {to: height}}, 
                          this.preanim(arguments, 4), 'motion');
        }
        return this;
    },
    
    
    setRegion : function(region, animate){
        this.setBounds(region.left, region.top, region.right-region.left, region.bottom-region.top, this.preanim(arguments, 1));
        return this;
    },

    
    addListener : function(eventName, fn, scope, options){
        Ext.EventManager.on(this.dom,  eventName, fn, scope || this, options);
    },

    
    removeListener : function(eventName, fn){
        Ext.EventManager.removeListener(this.dom,  eventName, fn);
        return this;
    },
    
    
    removeAllListeners : function(){
        E.purgeElement(this.dom);
        return this;
    },
    
    relayEvent : function(eventName, observable){
        this.on(eventName, function(e){
            observable.fireEvent(eventName, e);
        });  
    },
    
    
     setOpacity : function(opacity, animate){
        if(!animate || !A){
            var s = this.dom.style;
            if(Ext.isIE){
                s.zoom = 1;
                s.filter = (s.filter || '').replace(/alpha\([^\)]*\)/gi,"") +
                           (opacity == 1 ? "" : "alpha(opacity=" + opacity * 100 + ")");
            }else{
                s.opacity = opacity;
            }
        }else{
            this.anim({opacity: {to: opacity}}, this.preanim(arguments, 1), null, .35, 'easeIn');
        }
        return this;
    },
    
    
    getLeft : function(local){
        if(!local){
            return this.getX();
        }else{
            return parseInt(this.getStyle("left"), 10) || 0;
        }
    },
    
    
    getRight : function(local){
        if(!local){
            return this.getX() + this.getWidth();
        }else{
            return (this.getLeft(true) + this.getWidth()) || 0;
        }
    },
    
    
    getTop : function(local) {
        if(!local){
            return this.getY();
        }else{
            return parseInt(this.getStyle("top"), 10) || 0;
        }
    },
    
    
    getBottom : function(local){
        if(!local){
            return this.getY() + this.getHeight();
        }else{
            return (this.getTop(true) + this.getHeight()) || 0;
        }
    },

    
    position : function(pos, zIndex, x, y){
        if(!pos){
           if(this.getStyle('position') == 'static'){
               this.setStyle('position', 'relative');
           }
        }else{
            this.setStyle("position", pos);
        }
        if(zIndex){
            this.setStyle("z-index", zIndex);
        }
        if(x !== undefined && y !== undefined){
            this.setXY([x, y]);
        }else if(x !== undefined){
            this.setX(x);
        }else if(y !== undefined){
            this.setY(y);
        }
    },
    
    
    clearPositioning : function(value){
        value = value ||'';
        this.setStyle({
            "left": value,
            "right": value,
            "top": value,
            "bottom": value,
            "z-index": "",
            "position" : "static"
        });
        return this;
    },
    
    
    getPositioning : function(){
        var l = this.getStyle("left");
        var t = this.getStyle("top");
        return {
            "position" : this.getStyle("position"),
            "left" : l,
            "right" : l ? "" : this.getStyle("right"),
            "top" : t,
            "bottom" : t ? "" : this.getStyle("bottom"),
            "z-index" : this.getStyle("z-index")
        };
    },

    
    getBorderWidth : function(side){
        return this.addStyles(side, El.borders);
    },
    
    
    getPadding : function(side){
        return this.addStyles(side, El.paddings);
    },
    
    
    setPositioning : function(pc){
        this.applyStyles(pc);
        if(pc.right == "auto"){
            this.dom.style.right = "";
        }
        if(pc.bottom == "auto"){
            this.dom.style.bottom = "";
        }
        return this;
    },

    // private
    fixDisplay : function(){
        if(this.getStyle("display") == "none"){
            this.setStyle("visibility", "hidden");
            this.setStyle("display", this.originalDisplay); // first try reverting to default
            if(this.getStyle("display") == "none"){ // if that fails, default to block
                this.setStyle("display", "block");
            }
        }
    },

    
     setLeftTop : function(left, top){
        this.dom.style.left = this.addUnits(left);
        this.dom.style.top = this.addUnits(top);
        return this;
    },
    
    
     move : function(direction, distance, animate){
        var xy = this.getXY();
        direction = direction.toLowerCase();
        switch(direction){
            case "l":
            case "left":
                this.moveTo(xy[0]-distance, xy[1], this.preanim(arguments, 2));
                break;
           case "r":
           case "right":
                this.moveTo(xy[0]+distance, xy[1], this.preanim(arguments, 2));
                break;
           case "t":
           case "top":
           case "up":
                this.moveTo(xy[0], xy[1]-distance, this.preanim(arguments, 2));
                break;
           case "b":
           case "bottom":
           case "down":
                this.moveTo(xy[0], xy[1]+distance, this.preanim(arguments, 2));
                break;
        }
        return this;
    },
    
    
    clip : function(){
        if(!this.isClipped){
           this.isClipped = true;
           this.originalClip = {
               "o": this.getStyle("overflow"),
               "x": this.getStyle("overflow-x"),
               "y": this.getStyle("overflow-y")
           };
           this.setStyle("overflow", "hidden");
           this.setStyle("overflow-x", "hidden");
           this.setStyle("overflow-y", "hidden");
        }
        return this;
    },
    
    
    unclip : function(){
        if(this.isClipped){
            this.isClipped = false;
            var o = this.originalClip;
            if(o.o){this.setStyle("overflow", o.o);}
            if(o.x){this.setStyle("overflow-x", o.x);}
            if(o.y){this.setStyle("overflow-y", o.y);}
        }
        return this;
    },


    
    getAnchorXY : function(anchor, local, s){
        //Passing a different size is useful for pre-calculating anchors,
        //especially for anchored animations that change the el size.

        var w, h, vp = false;
        if(!s){
            var d = this.dom;
            if(d == document.body || d == document){
                vp = true;
                w = D.getViewWidth(); h = D.getViewHeight();
            }else{
                w = this.getWidth(); h = this.getHeight();
            }
        }else{
            w = s.width;  h = s.height;
        }
        var x = 0, y = 0, r = Math.round;
        switch((anchor || "tl").toLowerCase()){
            case "c":
                x = r(w*.5);
                y = r(h*.5);
            break;
            case "t":
                x = r(w*.5);
                y = 0;
            break;
            case "l":
                x = 0;
                y = r(h*.5);
            break;
            case "r":
                x = w;
                y = r(h*.5);
            break;
            case "b":
                x = r(w*.5);
                y = h;
            break;
            case "tl":
                x = 0;
                y = 0;
            break;
            case "bl":
                x = 0;
                y = h;
            break;
            case "br":
                x = w;
                y = h;
            break;
            case "tr":
                x = w;
                y = 0;
            break;
        }
        if(local === true){
            return [x, y];
        }
        if(vp){
            var sc = this.getScroll();
            return [x + sc.left, y + sc.top];
        }
        //Add the element's offset xy
        var o = this.getXY();
        return [x+o[0], y+o[1]];
    },

    
    getAlignToXY : function(el, p, o){
        el = Ext.get(el);
        var d = this.dom;
        if(!el.dom){
            throw "Element.alignTo with an element that doesn't exist";
        }
        var c = false; //constrain to viewport
        var p1 = "", p2 = "";
        o = o || [0,0];

        if(!p){
            p = "tl-bl";
        }else if(p == "?"){
            p = "tl-bl?";
        }else if(p.indexOf("-") == -1){
            p = "tl-" + p;
        }
        p = p.toLowerCase();
        var m = p.match(/^([a-z]+)-([a-z]+)(\?)?$/);
        if(!m){
           throw "Element.alignTo with an invalid alignment " + p;
        }
        p1 = m[1]; p2 = m[2]; c = !!m[3];

        //Subtract the aligned el's internal xy from the target's offset xy
        //plus custom offset to get the aligned el's new offset xy
        var a1 = this.getAnchorXY(p1, true);
        var a2 = el.getAnchorXY(p2, false);
        var x = a2[0] - a1[0] + o[0];
        var y = a2[1] - a1[1] + o[1];
        if(c){
            //constrain the aligned el to viewport if necessary
            var w = this.getWidth(), h = this.getHeight(), r = el.getRegion();
            // 5px of margin for ie
            var dw = D.getViewWidth()-5, dh = D.getViewHeight()-5;

            //If we are at a viewport boundary and the aligned el is anchored on a target border that is
            //perpendicular to the vp border, allow the aligned el to slide on that border,
            //otherwise swap the aligned el to the opposite border of the target.
            var p1y = p1.charAt(0), p1x = p1.charAt(p1.length-1);
           var p2y = p2.charAt(0), p2x = p2.charAt(p2.length-1);
           var swapY = ((p1y=="t" && p2y=="b") || (p1y=="b" && p2y=="t"));
           var swapX = ((p1x=="r" && p2x=="l") || (p1x=="l" && p2x=="r"));

           var doc = document;
           var scrollX = (doc.documentElement.scrollLeft || doc.body.scrollLeft || 0)+5;
           var scrollY = (doc.documentElement.scrollTop || doc.body.scrollTop || 0)+5;

           if((x+w) > dw + scrollX){
                x = swapX ? r.left-w : dw+scrollX-w;
            }
           if(x < scrollX){
               x = swapX ? r.right : scrollX;
           }
           if((y+h) > dh + scrollY){
                y = swapY ? r.top-h : dh+scrollY-h;
            }
           if (y < scrollY){
               y = swapY ? r.bottom : scrollY;
           }
        }
        return [x,y];
    },

    // private
    getConstrainToXY : function(){
        var os = {top:0, left:0, bottom:0, right: 0};

        return function(el, local, offsets){
            el = Ext.get(el);
            offsets = offsets ? Ext.applyIf(offsets, os) : os;

            var vw, vh, vx = 0, vy = 0;
            if(el.dom == document.body || el.dom == document){
                vw = Ext.lib.Dom.getViewWidth();
                vh = Ext.lib.Dom.getViewHeight();
            }else{
                vw = el.dom.clientWidth;
                vh = el.dom.clientHeight;
                if(!local){
                    var vxy = el.getXY();
                    vx = vxy[0];
                    vy = vxy[1];
                }
            }

            var s = el.getScroll();

            vx += offsets.left + s.left;
            vy += offsets.top + s.top;

            vw -= offsets.right;
            vh -= offsets.bottom;

            var vr = vx+vw;
            var vb = vy+vh;

            var xy = !local ? this.getXY() : [this.getLeft(true), this.getTop(true)];
            var x = xy[0], y = xy[1];
            var w = this.dom.offsetWidth, h = this.dom.offsetHeight;

            // only move it if it needs it
            var moved = false;

            // first validate right/bottom
            if((x + w) > vr){
                x = vr - w;
                moved = true;
            }
            if((y + h) > vb){
                y = vb - h;
                moved = true;
            }
            // then make sure top/left isn't negative
            if(x < vx){
                x = vx;
                moved = true;
            }
            if(y < vy){
                y = vy;
                moved = true;
            }
            return moved ? [x, y] : false;
        };
    }(),

    
    alignTo : function(element, position, offsets, animate){
        var xy = this.getAlignToXY(element, position, offsets);
        this.setXY(xy, this.preanim(arguments, 3));
        return this;
    },

    
    anchorTo : function(el, alignment, offsets, animate, monitorScroll, callback){
        var action = function(){
            this.alignTo(el, alignment, offsets, animate);
            Ext.callback(callback, this);
        };
        Ext.EventManager.onWindowResize(action, this);
        var tm = typeof monitorScroll;
        if(tm != 'undefined'){
            Ext.EventManager.on(window, 'scroll', action, this,
                {buffer: tm == 'number' ? monitorScroll : 50});
        }
        action.call(this); // align immediately
        return this;
    },
    
    clearOpacity : function(){
        if (window.ActiveXObject) {
            if(typeof this.dom.style.filter == 'string' && (/alpha/i).test(this.dom.style.filter)){
                this.dom.style.filter = "";
            }
        } else {
            this.dom.style.opacity = "";
            this.dom.style["-moz-opacity"] = "";
            this.dom.style["-khtml-opacity"] = "";
        }
        return this;
    },
    
    
    hide : function(animate){
        this.setVisible(false, this.preanim(arguments, 0));
        return this;
    },
    
    
    show : function(animate){
        this.setVisible(true, this.preanim(arguments, 0));
        return this;
    },
    
    
    addUnits : function(size){
        return Ext.Element.addUnits(size, this.defaultUnit);
    },
    
    
    beginMeasure : function(){
        var el = this.dom;
        if(el.offsetWidth || el.offsetHeight){
            return this; // offsets work already
        }
        var changed = [];
        var p = this.dom, b = document.body; // start with this element
        while((!el.offsetWidth && !el.offsetHeight) && p && p.tagName && p != b){
            var pe = Ext.get(p);
            if(pe.getStyle('display') == 'none'){
                changed.push({el: p, visibility: pe.getStyle("visibility")});
                p.style.visibility = "hidden";
                p.style.display = "block";
            }
            p = p.parentNode;
        }
        this._measureChanged = changed;
        return this;
               
    },
    
    
    endMeasure : function(){
        var changed = this._measureChanged;
        if(changed){
            for(var i = 0, len = changed.length; i < len; i++) {
            	var r = changed[i];
            	r.el.style.visibility = r.visibility;
                r.el.style.display = "none";
            }
            this._measureChanged = null;
        }
        return this;
    },
    
    
    update : function(html, loadScripts, callback){
        if(typeof html == "undefined"){
            html = "";
        }
        if(loadScripts !== true){
            this.dom.innerHTML = html;
            if(typeof callback == "function"){
                callback();
            }
            return this;
        }
        var id = Ext.id();
        var dom = this.dom;
        
        html += '<span id="' + id + '"></span>';
        
        E.onAvailable(id, function(){
            var hd = document.getElementsByTagName("head")[0];
            var re = /(?:<script([^>]*)?>)((\n|\r|.)*?)(?:<\/script>)/ig;
            var srcRe = /\ssrc=([\'\"])(.*?)\1/i;
            var typeRe = /\stype=([\'\"])(.*?)\1/i;
            
            var match;
            while(match = re.exec(html)){
                var attrs = match[1];
                var srcMatch = attrs ? attrs.match(srcRe) : false;
                if(srcMatch && srcMatch[2]){
                   var s = document.createElement("script");
                   s.src = srcMatch[2];
                   var typeMatch = attrs.match(typeRe);
                   if(typeMatch && typeMatch[2]){
                       s.type = typeMatch[2];
                   }
                   hd.appendChild(s);
                }else if(match[2] && match[2].length > 0){
                   eval(match[2]);
                }
            }
            var el = document.getElementById(id);
            if(el){el.parentNode.removeChild(el);}
            if(typeof callback == "function"){
                callback();
            }
        });
        dom.innerHTML = html.replace(/(?:<script.*?>)((\n|\r|.)*?)(?:<\/script>)/ig, "");
        return this;
    },
    
    
    load : function(){
        var um = this.getUpdateManager();
        um.update.apply(um, arguments);
        return this;
    },
    
    
    getUpdateManager : function(){
        if(!this.updateManager){
            this.updateManager = new Ext.UpdateManager(this);
        }
        return this.updateManager;
    },
    
    
    unselectable : function(){
        this.dom.unselectable = "on";
        this.swallowEvent("selectstart", true);
        this.applyStyles("-moz-user-select:none;-khtml-user-select:none;");
        this.addClass("x-unselectable");
        return this;
    },
    
    
    getCenterXY : function(){
        return this.getAlignToXY(document, 'c-c');
    },

    
    center : function(centerIn){
        this.alignTo(centerIn || document, 'c-c');
        return this;
    },

    
    isBorderBox : function(){
        return noBoxAdjust[this.dom.tagName.toLowerCase()] || Ext.isBorderBox;
    },
    
    
    getBox : function(contentBox, local){
        var xy;
        if(!local){
            xy = this.getXY();
        }else{
            var left = parseInt(this.getStyle("left"), 10) || 0;
            var top = parseInt(this.getStyle("top"), 10) || 0;
            xy = [left, top];
        }
        var el = this.dom, w = el.offsetWidth, h = el.offsetHeight, bx;
        if(!contentBox){
            bx = {x: xy[0], y: xy[1], 0: xy[0], 1: xy[1], width: w, height: h};
        }else{
            var l = this.getBorderWidth("l")+this.getPadding("l");
            var r = this.getBorderWidth("r")+this.getPadding("r");
            var t = this.getBorderWidth("t")+this.getPadding("t");
            var b = this.getBorderWidth("b")+this.getPadding("b");
            bx = {x: xy[0]+l, y: xy[1]+t, 0: xy[0]+l, 1: xy[1]+t, width: w-(l+r), height: h-(t+b)};
        }
        bx.right = bx.x + bx.width;
        bx.bottom = bx.y + bx.height;
        return bx;
    },

    
    getFrameWidth : function(sides, onlyContentBox){
        return onlyContentBox && Ext.isBorderBox ? 0 : (this.getPadding(sides) + this.getBorderWidth(sides));
    },

    
    setBox : function(box, adjust, animate){
        var w = box.width, h = box.height;
        if((adjust && !this.autoBoxAdjust) && !this.isBorderBox()){
           w -= (this.getBorderWidth("lr") + this.getPadding("lr"));
           h -= (this.getBorderWidth("tb") + this.getPadding("tb"));
        }
        this.setBounds(box.x, box.y, w, h, this.preanim(arguments, 2));
        return this;
    },
    
    
     repaint : function(){
        var dom = this.dom;
        this.addClass("x-repaint");
        setTimeout(function(){
            Ext.get(dom).removeClass("x-repaint");
        }, 1);
        return this;
    },
    
    
    getMargins : function(side){
        if(!side){
            return {
                top: parseInt(this.getStyle("margin-top"), 10) || 0,
                left: parseInt(this.getStyle("margin-left"), 10) || 0,
                bottom: parseInt(this.getStyle("margin-bottom"), 10) || 0,
                right: parseInt(this.getStyle("margin-right"), 10) || 0
            };
        }else{
            return this.addStyles(side, El.margins);
         }
    },

    // private
    addStyles : function(sides, styles){
        var val = 0;
        for(var i = 0, len = sides.length; i < len; i++){
             var w = parseInt(this.getStyle(styles[sides.charAt(i)]), 10);
             if(!isNaN(w)) val += w;
        }
        return val;
    },
    
    
    createProxy : function(config, renderTo, matchBox){
        if(renderTo){
            renderTo = Ext.getDom(renderTo);
        }else{
            renderTo = document.body;
        }
        config = typeof config == "object" ?
            config : {tag : "div", cls: config};
        var proxy = Ext.DomHelper.append(renderTo, config, true);
        if(matchBox){
           proxy.setBox(this.getBox());
        }
        return proxy;
    },
    
    
    mask : function(msg, msgCls){
        if(this.getStyle("position") == "static"){
            this.setStyle("position", "relative");
        }
        if(!this._mask){
            this._mask = Ext.DomHelper.append(this.dom, {tag:"div", cls:"ext-el-mask"}, true);
        }
        this.addClass("x-masked");
        this._mask.setDisplayed(true);
        if(typeof msg == 'string'){
            if(!this._maskMsg){
                this._maskMsg = Ext.DomHelper.append(this.dom, {tag:"div", cls:"ext-el-mask-msg", cn:{tag:'div'}}, true);
            }
            var mm = this._maskMsg;
            mm.dom.className = msgCls ? "ext-el-mask-msg " + msgCls : "ext-el-mask-msg";
            mm.dom.firstChild.innerHTML = msg;
            mm.setDisplayed(true);
            mm.center(this);
        }
        return this._mask;
    },
    
    
    unmask : function(removeEl){
        if(this._mask){
            if(removeEl === true){
                this._mask.remove();
                delete this._mask;
                if(this._maskMsg){
                    this._maskMsg.remove();
                    delete this._maskMsg;
                }
            }else{
                this._mask.setDisplayed(false);
                if(this._maskMsg){
                    this._maskMsg.setDisplayed(false);
                }
            }
        }
        this.removeClass("x-masked");
    },

    
    isMasked : function(){
        return this._mask && this._mask.isVisible();
    },

    
    createShim : function(){
        var el = document.createElement('iframe');
        el.frameBorder = 'no';
        el.className = 'ext-shim';
        if(Ext.isIE && Ext.isSecure){
            el.src = Ext.SSL_SECURE_URL;
        }
        var shim = Ext.get(this.dom.parentNode.insertBefore(el, this.dom));
        shim.autoBoxAdjust = false;
        return shim;
    },

    
    remove : function(){
        if(this.dom.parentNode){
            this.dom.parentNode.removeChild(this.dom);
        }
        delete El.cache[this.dom.id];
    },

    
    addClassOnOver : function(className, preventFlicker){
        this.on("mouseover", function(){
            Ext.fly(this, '_internal').addClass(className);
        }, this.dom);
        var removeFn = function(e){
            if(preventFlicker !== true || !e.within(this, true)){
                Ext.fly(this, '_internal').removeClass(className);
            }
        };
        this.on("mouseout", removeFn, this.dom);
        return this;
    },

    
    addClassOnFocus : function(className){
        this.on("focus", function(){
            Ext.fly(this, '_internal').addClass(className);
        }, this.dom);
        this.on("blur", function(){
            Ext.fly(this, '_internal').removeClass(className);
        }, this.dom);
        return this;
    },
    
    addClassOnClick : function(className){
        var dom = this.dom;
        this.on("mousedown", function(){
            Ext.fly(dom, '_internal').addClass(className);
            var d = Ext.get(document);
            var fn = function(){
                Ext.fly(dom, '_internal').removeClass(className);
                d.removeListener("mouseup", fn);
            };
            d.on("mouseup", fn);
        });
        return this;
    },

    
    swallowEvent : function(eventName, preventDefault){
        var fn = function(e){
            e.stopPropagation();
            if(preventDefault){
                e.preventDefault();
            }
        };
        if(eventName instanceof Array){
            for(var i = 0, len = eventName.length; i < len; i++){
                 this.on(eventName[i], fn);
            }
            return this;
        }
        this.on(eventName, fn);
        return this;
    },

    
    fitToParent : function(monitorResize, targetParent){
        var p = Ext.get(targetParent || this.dom.parentNode);
        this.setSize(p.getComputedWidth()-p.getFrameWidth('lr'), p.getComputedHeight()-p.getFrameWidth('tb'));
        if(monitorResize === true){
            Ext.EventManager.onWindowResize(this.fitToParent.createDelegate(this, []));
        }
        return this;
    },
    
    
    getNextSibling : function(){
        var n = this.dom.nextSibling;
        while(n && n.nodeType != 1){
            n = n.nextSibling;
        }
        return n;
    },
    
    
    getPrevSibling : function(){
        var n = this.dom.previousSibling;
        while(n && n.nodeType != 1){
            n = n.previousSibling;
        }
        return n;
    },
    
    
    
    appendChild: function(el){
        el = Ext.get(el);
        el.appendTo(this);
        return this;
    },
    
    
    createChild: function(config, insertBefore, returnDom){
        config = config || {tag:'div'};
        if(insertBefore){
            return Ext.DomHelper.insertBefore(insertBefore, config, returnDom !== true);
        }
        return Ext.DomHelper[!this.dom.firstChild ? 'overwrite' : 'append'](this.dom, config,  returnDom !== true);
    },
    
    
    appendTo: function(el){
        el = Ext.getDom(el);
        el.appendChild(this.dom);
        return this;
    },
    
    
    insertBefore: function(el){
        el = Ext.getDom(el);
        el.parentNode.insertBefore(this.dom, el);
        return this;
    },
    
    
    insertAfter: function(el){
        el = Ext.getDom(el);
        el.parentNode.insertBefore(this.dom, el.nextSibling);
        return this;
    },

    
    insertFirst: function(el, returnDom){
        el = el || {};
        if(typeof el == 'object' && !el.nodeType){ // dh config
            return this.createChild(el, this.dom.firstChild, returnDom);
        }else{
            el = Ext.getDom(el);
            this.dom.insertBefore(el, this.dom.firstChild);
            return !returnDom ? Ext.get(el) : el;
        }
    },

    
    insertSibling: function(el, where, returnDom){
        where = where ? where.toLowerCase() : 'before';
        el = el || {};
        var rt, refNode = where == 'before' ? this.dom : this.dom.nextSibling;

        if(typeof el == 'object' && !el.nodeType){ // dh config
            if(where == 'after' && !this.dom.nextSibling){
                rt = Ext.DomHelper.append(this.dom.parentNode, el, !returnDom);
            }else{
                rt = Ext.DomHelper[where == 'after' ? 'insertAfter' : 'insertBefore'](this.dom, el, !returnDom);
            }

        }else{
            rt = this.dom.parentNode.insertBefore(Ext.getDom(el),
                        where == 'before' ? this.dom : this.dom.nextSibling);
            if(!returnDom){
                rt = Ext.get(rt);
            }
        }
        return rt;
    },

    
    wrap: function(config, returnDom){
        if(!config){
            config = {tag: "div"};
        }
        var newEl = Ext.DomHelper.insertBefore(this.dom, config, !returnDom);
        newEl.dom ? newEl.dom.appendChild(this.dom) : newEl.appendChild(this.dom);
        return newEl;
    },
    
    
    replace: function(el){
        el = Ext.get(el);
        this.insertBefore(el);
        el.remove();
        return this;
    },
    
    
    insertHtml : function(where, html, returnEl){
        var el = Ext.DomHelper.insertHtml(where, this.dom, html);
        return returnEl ? Ext.get(el) : el;
    },
    
    
    set : function(o, useSet){
        var el = this.dom;
        useSet = typeof useSet == 'undefined' ? (el.setAttribute ? true : false) : useSet;
        for(var attr in o){
            if(attr == "style" || typeof o[attr] == "function") continue;
            if(attr=="cls"){
                el.className = o["cls"];
            }else{
                if(useSet) el.setAttribute(attr, o[attr]);
                else el[attr] = o[attr];
            }
        }
        Ext.DomHelper.applyStyles(el, o.style);
        return this;
    },
    
    
    addKeyListener : function(key, fn, scope){
        var config;
        if(typeof key != "object" || key instanceof Array){
            config = {
                key: key,
                fn: fn,
                scope: scope 
            };
        }else{
            config = {
                key : key.key,
                shift : key.shift,
                ctrl : key.ctrl,
                alt : key.alt,
                fn: fn,
                scope: scope
            };
        }
        return new Ext.KeyMap(this, config);
    },
    
    
    addKeyMap : function(config){
        return new Ext.KeyMap(this, config);
    },
    
    
     isScrollable : function(){
        var dom = this.dom;
        return dom.scrollHeight > dom.clientHeight || dom.scrollWidth > dom.clientWidth;
    },
    
    
     
    scrollTo : function(side, value, animate){
        var prop = side.toLowerCase() == "left" ? "scrollLeft" : "scrollTop";
        if(!animate || !A){
            this.dom[prop] = value;
        }else{
            var to = prop == "scrollLeft" ? [value, this.dom.scrollTop] : [this.dom.scrollLeft, value];
            this.anim({scroll: {"to": to}}, this.preanim(arguments, 2), 'scroll');
        }
        return this;
    },
    
    
     scroll : function(direction, distance, animate){
         if(!this.isScrollable()){
             return;
         }
         var el = this.dom;
         var l = el.scrollLeft, t = el.scrollTop;
         var w = el.scrollWidth, h = el.scrollHeight;
         var cw = el.clientWidth, ch = el.clientHeight;
         direction = direction.toLowerCase();
         var scrolled = false;
         var a = this.preanim(arguments, 2);
         switch(direction){
             case "l":
             case "left":
                 if(w - l > cw){
                     var v = Math.min(l + distance, w-cw);
                     this.scrollTo("left", v, a);
                     scrolled = true;
                 }
                 break;
            case "r":
            case "right":
                 if(l > 0){
                     var v = Math.max(l - distance, 0);
                     this.scrollTo("left", v, a);
                     scrolled = true;
                 }
                 break;
            case "t":
            case "top":
            case "up":
                 if(t > 0){
                     var v = Math.max(t - distance, 0);
                     this.scrollTo("top", v, a);
                     scrolled = true;
                 }
                 break;
            case "b":
            case "bottom":
            case "down":
                 if(h - t > ch){
                     var v = Math.min(t + distance, h-ch);
                     this.scrollTo("top", v, a);
                     scrolled = true;
                 }
                 break;
         }
         return scrolled;
    },

    
    translatePoints : function(x, y){
        if(typeof x == 'object' || x instanceof Array){
            y = x[1]; x = x[0];
        }
        var p = this.getStyle('position');
        var o = this.getXY();

        var l = parseInt(this.getStyle('left'), 10);
        var t = parseInt(this.getStyle('top'), 10);

        if(isNaN(l)){
            l = (p == "relative") ? 0 : this.dom.offsetLeft;
        }
        if(isNaN(t)){
            t = (p == "relative") ? 0 : this.dom.offsetTop;
        }

        return {left: (x - o[0] + l), top: (y - o[1] + t)};
    },

    
    getScroll : function(){
        var d = this.dom, doc = document;
        if(d == doc || d == doc.body){
            var l = window.pageXOffset || doc.documentElement.scrollLeft || doc.body.scrollLeft || 0;
            var t = window.pageYOffset || doc.documentElement.scrollTop || doc.body.scrollTop || 0;
            return {left: l, top: t};
        }else{
            return {left: d.scrollLeft, top: d.scrollTop};
        }
    },

    
    getColor : function(attr, defaultValue, prefix){
        var v = this.getStyle(attr);
        if(!v || v == "transparent" || v == "inherit") {
            return defaultValue;
        }
        var color = typeof prefix == "undefined" ? "#" : prefix;
        if(v.substr(0, 4) == "rgb("){
            var rvs = v.slice(4, v.length -1).split(",");
            for(var i = 0; i < 3; i++){
                var h = parseInt(rvs[i]).toString(16);
                if(h < 16){
                    h = "0" + h;
                }
                color += h;
            }
        } else {  
            if(v.substr(0, 1) == "#"){
                if(v.length == 4) {
                    for(var i = 1; i < 4; i++){
                        var c = v.charAt(i);
                        color +=  c + c;
                    }
                }else if(v.length == 7){
                    color += v.substr(1);
                }
            }
        }
        return(color.length > 5 ? color.toLowerCase() : defaultValue);  
    },

    
    boxWrap : function(cls){
        cls = cls || 'x-box';
        var el = Ext.get(this.insertHtml('beforeBegin', String.format('<div class="{0}">'+El.boxMarkup+'</div>', cls)));
        el.child('.'+cls+'-mc').dom.appendChild(this.dom);
        return el;
    },

    
    getAttributeNS : Ext.isIE ? function(ns, name){
        var d = this.dom;
        var type = typeof d[ns+":"+name];
        if(type != 'undefined' && type != 'unknown'){
            return d[ns+":"+name];
        }
        return d[name];
    } : function(ns, name){
        var d = this.dom;
        return d.getAttributeNS(ns, name) || d.getAttribute(ns+":"+name) || d.getAttribute(name) || d[name];
    }
};

var ep = El.prototype;


ep.on = ep.addListener;
    // backwards compat
ep.mon = ep.addListener;


ep.un = ep.removeListener;


ep.autoBoxAdjust = true;

ep.autoDisplayMode = true;

// private
El.unitPattern = /\d+(px|em|%|en|ex|pt|in|cm|mm|pc)$/i;

// private
El.addUnits = function(v, defaultUnit){
    if(v === "" || v == "auto"){
        return v;
    }
    if(v === undefined){
        return '';
    }
    if(typeof v == "number" || !El.unitPattern.test(v)){
        return v + (defaultUnit || 'px');
    }
    return v;
};

// special markup used throughout Ext when box wrapping elements
El.boxMarkup = '<div class="{0}-tl"><div class="{0}-tr"><div class="{0}-tc"></div></div></div><div class="{0}-ml"><div class="{0}-mr"><div class="{0}-mc"></div></div></div><div class="{0}-bl"><div class="{0}-br"><div class="{0}-bc"></div></div></div>';

El.VISIBILITY = 1;

El.DISPLAY = 2;

El.borders = {l: "border-left-width", r: "border-right-width", t: "border-top-width", b: "border-bottom-width"};
El.paddings = {l: "padding-left", r: "padding-right", t: "padding-top", b: "padding-bottom"};
El.margins = {l: "margin-left", r: "margin-right", t: "margin-top", b: "margin-bottom"};




El.cache = {};

var docEl;


El.get = function(el){
    var ex, elm, id;
    if(!el){ return null; }
    if(typeof el == "string"){ // element id
        if(!(elm = document.getElementById(el))){
            return null;
        }
        if(ex = El.cache[el]){
            ex.dom = elm;
        }else{
            ex = El.cache[el] = new El(elm);
        }
        return ex;
    }else if(el.tagName){ // dom element
        if(!(id = el.id)){
            id = Ext.id(el);
        }
        if(ex = El.cache[id]){
            ex.dom = el;
        }else{
            ex = El.cache[id] = new El(el);
        }
        return ex;
    }else if(el instanceof El){
        if(el != docEl){
            el.dom = document.getElementById(el.id) || el.dom; // refresh dom element in case no longer valid,
                                                          // catch case where it hasn't been appended
            El.cache[el.id] = el; // in case it was created directly with Element(), let's cache it
        }
        return el;
    }else if(el.isComposite){
        return el;
    }else if(el instanceof Array){
        return El.select(el);
    }else if(el == document){
        // create a bogus element object representing the document object
        if(!docEl){
            var f = function(){};
            f.prototype = El.prototype;
            docEl = new f();
            docEl.dom = document;
        }
        return docEl;
    }
    return null;
};

El.uncache = function(el){
    for(var i = 0, a = arguments, len = a.length; i < len; i++) {
        if(a[i]){
            delete El.cache[a[i].id || a[i]];
        }
    }
};


// Garbage collection - uncache elements/purge listeners on orphaned elements
// so we don't hold a reference and cause the browser to retain them
El.garbageCollect = function(){
    if(!Ext.enableGarbageCollector){
        clearInterval(El.collectorThread);
        return;
    }
    for(var eid in El.cache){
        var el = El.cache[eid], d = el.dom;
        // -------------------------------------------------------
        // Determining what is garbage:
        // -------------------------------------------------------
        // !d
        // dom node is null, definitely garbage
        // -------------------------------------------------------
        // !d.parentNode
        // no parentNode == direct orphan, definitely garbage
        // -------------------------------------------------------
        // !d.offsetParent && !document.getElementById(eid)
        // display none elements have no offsetParent so we will
        // also try to look it up by it's id. However, check
        // offsetParent first so we don't do unneeded lookups.
        // This enables collection of elements that are not orphans
        // directly, but somewhere up the line they have an orphan
        // parent.
        // -------------------------------------------------------
        if(!d || !d.parentNode || (!d.offsetParent && !document.getElementById(eid))){
            delete El.cache[eid];
            if(Ext.enableListenerCollection){
                Ext.lib.Dom.purgeElement(d);
            }
        }
    }
}
El.collectorThreadId = setInterval(El.garbageCollect, 30000);


// dom is optional
El.Flyweight = function(dom){
    this.dom = dom;
};
El.Flyweight.prototype = El.prototype;

El._flyweights = {};

El.fly = function(el, named){
    named = named || '_global';
    el = Ext.getDom(el);
    if(!el){
        return null;
    }
    if(!El._flyweights[named]){
        El._flyweights[named] = new El.Flyweight();
    }
    El._flyweights[named].dom = el;
    return El._flyweights[named];
};


Ext.get = El.get;

Ext.fly = El.fly;

// speedy lookup for elements never to box adjust
var noBoxAdjust = Ext.isStrict ? {
    select:1
} : {
    input:1, select:1, textarea:1
};
if(Ext.isIE || Ext.isGecko){
    noBoxAdjust['button'] = 1;
}


Ext.EventManager.on(window, 'unload', function(){
    delete El.cache;
    delete El._flyweights;
});
})();



//Notifies Element that fx methods are available
Ext.enableFx = true;


Ext.Fx = {
	
    slideIn : function(anchor, o){
        var el = this.getFxEl();
        o = o || {};

        el.queueFx(o, function(){

            anchor = anchor || "t";

            // fix display to visibility
            this.fixDisplay();

            // restore values after effect
            var r = this.getFxRestore();
            var b = this.getBox();
            // fixed size for slide
            this.setSize(b);

            // wrap if needed
            var wrap = this.fxWrap(r.pos, o, "hidden");

            var st = this.dom.style;
            st.visibility = "visible";
            st.position = "absolute";

            // clear out temp styles after slide and unwrap
            var after = function(){
                el.fxUnwrap(wrap, r.pos, o);
                st.width = r.width;
                st.height = r.height;
                el.afterFx(o);
            };
            // time to calc the positions
            var a, pt = {to: [b.x, b.y]}, bw = {to: b.width}, bh = {to: b.height};

            switch(anchor.toLowerCase()){
                case "t":
                    wrap.setSize(b.width, 0);
                    st.left = st.bottom = "0";
                    a = {height: bh};
                break;
                case "l":
                    wrap.setSize(0, b.height);
                    st.right = st.top = "0";
                    a = {width: bw};
                break;
                case "r":
                    wrap.setSize(0, b.height);
                    wrap.setX(b.right);
                    st.left = st.top = "0";
                    a = {width: bw, points: pt};
                break;
                case "b":
                    wrap.setSize(b.width, 0);
                    wrap.setY(b.bottom);
                    st.left = st.top = "0";
                    a = {height: bh, points: pt};
                break;
                case "tl":
                    wrap.setSize(0, 0);
                    st.right = st.bottom = "0";
                    a = {width: bw, height: bh};
                break;
                case "bl":
                    wrap.setSize(0, 0);
                    wrap.setY(b.y+b.height);
                    st.right = st.top = "0";
                    a = {width: bw, height: bh, points: pt};
                break;
                case "br":
                    wrap.setSize(0, 0);
                    wrap.setXY([b.right, b.bottom]);
                    st.left = st.top = "0";
                    a = {width: bw, height: bh, points: pt};
                break;
                case "tr":
                    wrap.setSize(0, 0);
                    wrap.setX(b.x+b.width);
                    st.left = st.bottom = "0";
                    a = {width: bw, height: bh, points: pt};
                break;
            }
            this.dom.style.visibility = "visible";
            wrap.show();

            arguments.callee.anim = wrap.fxanim(a,
                o,
                'motion',
                .5,
                'easeOut', after);
        });
        return this;
    },
    
	
    slideOut : function(anchor, o){
        var el = this.getFxEl();
        o = o || {};

        el.queueFx(o, function(){

            anchor = anchor || "t";

            // restore values after effect
            var r = this.getFxRestore();
            
            var b = this.getBox();
            // fixed size for slide
            this.setSize(b);

            // wrap if needed
            var wrap = this.fxWrap(r.pos, o, "visible");

            var st = this.dom.style;
            st.visibility = "visible";
            st.position = "absolute";

            wrap.setSize(b);

            var after = function(){
                if(o.useDisplay){
                    el.setDisplayed(false);
                }else{
                    el.hide();
                }

                el.fxUnwrap(wrap, r.pos, o);

                st.width = r.width;
                st.height = r.height;

                el.afterFx(o);
            };

            var a, zero = {to: 0};
            switch(anchor.toLowerCase()){
                case "t":
                    st.left = st.bottom = "0";
                    a = {height: zero};
                break;
                case "l":
                    st.right = st.top = "0";
                    a = {width: zero};
                break;
                case "r":
                    st.left = st.top = "0";
                    a = {width: zero, points: {to:[b.right, b.y]}};
                break;
                case "b":
                    st.left = st.top = "0";
                    a = {height: zero, points: {to:[b.x, b.bottom]}};
                break;
                case "tl":
                    st.right = st.bottom = "0";
                    a = {width: zero, height: zero};
                break;
                case "bl":
                    st.right = st.top = "0";
                    a = {width: zero, height: zero, points: {to:[b.x, b.bottom]}};
                break;
                case "br":
                    st.left = st.top = "0";
                    a = {width: zero, height: zero, points: {to:[b.x+b.width, b.bottom]}};
                break;
                case "tr":
                    st.left = st.bottom = "0";
                    a = {width: zero, height: zero, points: {to:[b.right, b.y]}};
                break;
            }

            arguments.callee.anim = wrap.fxanim(a,
                o,
                'motion',
                .5,
                "easeOut", after);
        });
        return this;
    },

	
    puff : function(o){
        var el = this.getFxEl();
        o = o || {};

        el.queueFx(o, function(){
            this.clearOpacity();
            this.show();

            // restore values after effect
            var r = this.getFxRestore();
            var st = this.dom.style;

            var after = function(){
                if(o.useDisplay){
                    el.setDisplayed(false);
                }else{
                    el.hide();
                }

                el.clearOpacity();

                el.setPositioning(r.pos);
                st.width = r.width;
                st.height = r.height;
                st.fontSize = '';
                el.afterFx(o);
            };

            var width = this.getWidth();
            var height = this.getHeight();

            arguments.callee.anim = this.fxanim({
                    width : {to: this.adjustWidth(width * 2)},
                    height : {to: this.adjustHeight(height * 2)},
                    points : {by: [-(width * .5), -(height * .5)]},
                    opacity : {to: 0},
                    fontSize: {to:200, unit: "%"}
                },
                o,
                'motion',
                .5,
                "easeOut", after);
        });
        return this;
    },

	
    switchOff : function(o){
        var el = this.getFxEl();
        o = o || {};

        el.queueFx(o, function(){
            this.clearOpacity();
            this.clip();

            // restore values after effect
            var r = this.getFxRestore();
            var st = this.dom.style;

            var after = function(){
                if(o.useDisplay){
                    el.setDisplayed(false);
                }else{
                    el.hide();
                }

                el.clearOpacity();
                el.setPositioning(r.pos);
                st.width = r.width;
                st.height = r.height;

                el.afterFx(o);
            };

            this.fxanim({opacity:{to:0.3}}, null, null, .1, null, function(){
                this.clearOpacity();
                (function(){
                    this.fxanim({
                        height:{to:1},
                        points:{by:[0, this.getHeight() * .5]}
                    }, o, 'motion', 0.3, 'easeIn', after);
                }).defer(100, this);
            });
        });
        return this;
    },

    	
    highlight : function(color, o){
        var el = this.getFxEl();
        o = o || {};

        el.queueFx(o, function(){
            color = color || "ffff9c";
            attr = o.attr || "backgroundColor";

            this.clearOpacity();
            this.show();

            var origColor = this.getColor(attr);
            var restoreColor = this.dom.style[attr];
            endColor = (o.endColor || origColor) || "ffffff";

            var after = function(){
                el.dom.style[attr] = restoreColor;
                el.afterFx(o);
            };

            var a = {};
            a[attr] = {from: color, to: endColor};
            arguments.callee.anim = this.fxanim(a,
                o,
                'color',
                1,
                'easeIn', after);
        });
        return this;
    },

   
    frame : function(color, count, o){
        var el = this.getFxEl();
        o = o || {};

        el.queueFx(o, function(){
            color = color || "#C3DAF9";
            if(color.length == 6){
                color = "#" + color;
            }
            count = count || 1;
            duration = o.duration || 1;
            this.show();

            var b = this.getBox();
            var animFn = function(){
                var proxy = this.createProxy({
                     tag:"div",
                     style:{
                        visbility:"hidden",
                        position:"absolute",
                        "z-index":"35000", // yee haw
                        border:"0px solid " + color
                     }
                  });
                var scale = Ext.isBorderBox ? 2 : 1;
                proxy.animate({
                    top:{from:b.y, to:b.y - 20},
                    left:{from:b.x, to:b.x - 20},
                    borderWidth:{from:0, to:10},
                    opacity:{from:1, to:0},
                    height:{from:b.height, to:(b.height + (20*scale))},
                    width:{from:b.width, to:(b.width + (20*scale))}
                }, duration, function(){
                    proxy.remove();
                });
                if(--count > 0){
                     animFn.defer((duration/2)*1000, this);
                }else{
                    el.afterFx(o);
                }
            };
            animFn.call(this);
        });
        return this;
    },

   
    pause : function(seconds){
        var el = this.getFxEl();
        var o = {};

        el.queueFx(o, function(){
            setTimeout(function(){
                el.afterFx(o);
            }, seconds * 1000);
        });
        return this;
    },

   
    fadeIn : function(o){
        var el = this.getFxEl();
        o = o || {};
        el.queueFx(o, function(){
            this.setOpacity(0);
            this.fixDisplay();
            this.dom.style.visibility = 'visible';
            var to = o.endOpacity || 1;
            arguments.callee.anim = this.fxanim({opacity:{to:to}},
                o, null, .5, "easeOut", function(){
                if(to == 1){
                    this.clearOpacity();
                }
                el.afterFx(o);
            });
        });
        return this;
    },

   
    fadeOut : function(o){
        var el = this.getFxEl();
        o = o || {};
        el.queueFx(o, function(){
            arguments.callee.anim = this.fxanim({opacity:{to:o.endOpacity || 0}},
                o, null, .5, "easeOut", function(){
                if(this.visibilityMode == Ext.Element.DISPLAY || o.useDisplay){
                     this.dom.style.display = "none";
                }else{
                     this.dom.style.visibility = "hidden";
                }
                this.clearOpacity();
                el.afterFx(o);
            });
        });
        return this;
    },

   
    scale : function(w, h, o){
        this.shift(Ext.apply({}, o, {
            width: w,
            height: h
        }));
        return this;
    },

   
    shift : function(o){
        var el = this.getFxEl();
        o = o || {};
        el.queueFx(o, function(){
            var a = {}, w = o.width, h = o.height, x = o.x, y = o.y,  op = o.opacity;
            if(w !== undefined){
                a.width = {to: this.adjustWidth(w)};
            }
            if(h !== undefined){
                a.height = {to: this.adjustHeight(h)};
            }
            if(x !== undefined || y !== undefined){
                a.points = {to: [
                    x !== undefined ? x : this.getX(),
                    y !== undefined ? y : this.getY()
                ]};
            }
            if(op !== undefined){
                a.opacity = {to: op};
            }
            if(o.xy !== undefined){
                a.points = {to: o.xy};
            }
            arguments.callee.anim = this.fxanim(a,
                o, 'motion', .35, "easeOut", function(){
                el.afterFx(o);
            });
        });
        return this;
    },

	
    ghost : function(anchor, o){
        var el = this.getFxEl();
        o = o || {};

        el.queueFx(o, function(){
            anchor = anchor || "b";

            // restore values after effect
            var r = this.getFxRestore();
            var w = this.getWidth(),
                h = this.getHeight();

            var st = this.dom.style;

            var after = function(){
                if(o.useDisplay){
                    el.setDisplayed(false);
                }else{
                    el.hide();
                }

                el.clearOpacity();
                el.setPositioning(r.pos);
                st.width = r.width;
                st.height = r.height;

                el.afterFx(o);
            };

            var a = {opacity: {to: 0}, points: {}}, pt = a.points;
            switch(anchor.toLowerCase()){
                case "t":
                    pt.by = [0, -h];
                break;
                case "l":
                    pt.by = [-w, 0];
                break;
                case "r":
                    pt.by = [w, 0];
                break;
                case "b":
                    pt.by = [0, h];
                break;
                case "tl":
                    pt.by = [-w, -h];
                break;
                case "bl":
                    pt.by = [-w, h];
                break;
                case "br":
                    pt.by = [w, h];
                break;
                case "tr":
                    pt.by = [w, -h];
                break;
            }

            arguments.callee.anim = this.fxanim(a,
                o,
                'motion',
                .5,
                "easeOut", after);
        });
        return this;
    },

	
    syncFx : function(){
        this.fxDefaults = Ext.apply(this.fxDefaults || {}, {
            block : false,
            concurrent : true,
            stopFx : false
        });
        return this;
    },

	
    sequenceFx : function(){
        this.fxDefaults = Ext.apply(this.fxDefaults || {}, {
            block : false,
            concurrent : false,
            stopFx : false
        });
        return this;
    },

	
    nextFx : function(){
        var ef = this.fxQueue[0];
        if(ef){
            ef.call(this);
        }
    },

	
    hasActiveFx : function(){
        return this.fxQueue && this.fxQueue[0];
    },

	
    stopFx : function(){
        if(this.hasActiveFx()){
            var cur = this.fxQueue[0];
            if(cur && cur.anim && cur.anim.isAnimated()){
                this.fxQueue = [cur]; // clear out others
                cur.anim.stop(true);
            }
        }
        return this;
    },

	
    beforeFx : function(o){
        if(this.hasActiveFx() && !o.concurrent){
           if(o.stopFx){
               this.stopFx();
               return true;
           }
           return false;
        }
        return true;
    },

	
    hasFxBlock : function(){
        var q = this.fxQueue;
        return q && q[0] && q[0].block;
    },

	
    queueFx : function(o, fn){
        if(!this.fxQueue){
            this.fxQueue = [];
        }
        if(!this.hasFxBlock()){
            Ext.applyIf(o, this.fxDefaults);
            if(!o.concurrent){
                var run = this.beforeFx(o);
                fn.block = o.block;
                this.fxQueue.push(fn);
                if(run){
                    this.nextFx();
                }
            }else{
                fn.call(this);
            }
        }
        return this;
    },

	
    fxWrap : function(pos, o, vis){
        var wrap;
        if(!o.wrap || !(wrap = Ext.get(o.wrap))){
            var wrapXY;
            if(o.fixPosition){
                wrapXY = this.getXY();
            }
            var div = document.createElement("div");
            div.style.visibility = vis;
            wrap = Ext.get(this.dom.parentNode.insertBefore(div, this.dom));
            wrap.setPositioning(pos);
            if(wrap.getStyle("position") == "static"){
                wrap.position("relative");
            }
            this.clearPositioning('auto');
            wrap.clip();
            wrap.dom.appendChild(this.dom);
            if(wrapXY){
                wrap.setXY(wrapXY);
            }
        }
        return wrap;
    },

	
    fxUnwrap : function(wrap, pos, o){
        this.clearPositioning();
        this.setPositioning(pos);
        if(!o.wrap){
            wrap.dom.parentNode.insertBefore(this.dom, wrap.dom);
            wrap.remove();
        }
    },

	
    getFxRestore : function(){
        var st = this.dom.style;
        return {pos: this.getPositioning(), width: st.width, height : st.height};
    },

	
    afterFx : function(o){
        if(o.afterStyle){
            this.applyStyles(o.afterStyle);
        }
        if(o.afterCls){
            this.addClass(o.afterCls);
        }
        if(o.remove === true){
            this.remove();
        }
        Ext.callback(o.callback, o.scope, [this]);
        if(!o.concurrent){
            this.fxQueue.shift();
            this.nextFx();
        }
    },

	
    getFxEl : function(){ // support for composite element fx
        return Ext.get(this.dom);
    },

	
    fxanim : function(args, opt, animType, defaultDur, defaultEase, cb){
        animType = animType || 'run';
        opt = opt || {};
        var anim = Ext.lib.Anim[animType](
            this.dom, args,
            (opt.duration || defaultDur) || .35,
            (opt.easing || defaultEase) || 'easeOut',
            function(){
                Ext.callback(cb, this);
            },
            this
        );
        opt.anim = anim;
        return anim;
    }
};

// backwords compat
Ext.Fx.resize = Ext.Fx.scale;

//When included, Ext.Fx is automatically applied to Element so that all basic
//effects are available directly via the Element API
Ext.apply(Ext.Element.prototype, Ext.Fx);


Ext.CompositeElement = function(els){
    this.elements = [];
    this.addElements(els);
};
Ext.CompositeElement.prototype = {
    isComposite: true,
    addElements : function(els){
        if(!els) return this;
        if(typeof els == "string"){
            els = Ext.Element.selectorFunction(els);
        }
        var yels = this.elements;
        var index = yels.length-1;
        for(var i = 0, len = els.length; i < len; i++) {
        	yels[++index] = Ext.get(els[i], true);
        }
        return this;
    },
    invoke : function(fn, args){
        var els = this.elements;
        for(var i = 0, len = els.length; i < len; i++) {
        	Ext.Element.prototype[fn].apply(els[i], args);
        }
        return this;
    },
    
    add : function(els){
        if(typeof els == "string"){
            this.addElements(Ext.Element.selectorFunction(els));
        }else if(els.length !== undefined){
            this.addElements(els);
        }else{
            this.addElements([els]);
        }
        return this;
    },
    
    each : function(fn, scope){
        var els = this.elements;
        for(var i = 0, len = els.length; i < len; i++){
            if(fn.call(scope || els[i], els[i], this, i) === false) {
                break;
            }
        }
        return this;
    },

    
    item : function(index){
        return this.elements[index];
    }
};
(function(){
Ext.CompositeElement.createCall = function(proto, fnName){
    if(!proto[fnName]){
        proto[fnName] = function(){
            return this.invoke(fnName, arguments);
        };
    }
};
for(var fnName in Ext.Element.prototype){
    if(typeof Ext.Element.prototype[fnName] == "function"){
        Ext.CompositeElement.createCall(Ext.CompositeElement.prototype, fnName);
    }
};
})();


Ext.CompositeElementLite = function(els){
    Ext.CompositeElementLite.superclass.constructor.call(this, els);
    var flyEl = function(){};
    flyEl.prototype = Ext.Element.prototype;
    this.el = new Ext.Element.Flyweight();
};
Ext.extend(Ext.CompositeElementLite, Ext.CompositeElement, {
    addElements : function(els){
        if(els){
            if(els instanceof Array){
                this.elements = this.elements.concat(els);
            }else{
                var yels = this.elements;
                var index = yels.length-1;
                for(var i = 0, len = els.length; i < len; i++) {
                    yels[++index] = els[i];
                }
            }
        }
        return this;
    },
    invoke : function(fn, args){
        var els = this.elements;
        var el = this.el;
        for(var i = 0, len = els.length; i < len; i++) {
            el.dom = els[i];
        	Ext.Element.prototype[fn].apply(el, args);
        }
        return this;
    },
    
    item : function(index){
        this.el.dom = this.elements[index];
        return this.el;
    },

    // fixes scope with flyweight
    addListener : function(eventName, handler, scope, opt){
        var els = this.elements;
        for(var i = 0, len = els.length; i < len; i++) {
            Ext.EventManager.on(els[i], eventName, handler, scope || els[i], opt);
        }
        return this;
    },

    
    each : function(fn, scope){
        var els = this.elements;
        var el = this.el;
        for(var i = 0, len = els.length; i < len; i++){
            el.dom = els[i];
        	if(fn.call(scope || el, el, this, i) === false){
                break;
            }
        }
        return this;
    }
});
Ext.CompositeElementLite.prototype.on = Ext.CompositeElementLite.prototype.addListener;
if(Ext.DomQuery){
    Ext.Element.selectorFunction = Ext.DomQuery.select;
}

Ext.Element.select = function(selector, unique, root){
    var els;
    if(typeof selector == "string"){
        els = Ext.Element.selectorFunction(selector, root);
    }else if(selector.length !== undefined){
        els = selector;
    }else{
        throw "Invalid selector";
    }
    if(unique === true){
        return new Ext.CompositeElement(els);
    }else{
        return new Ext.CompositeElementLite(els);
    }
};

Ext.select = Ext.Element.select;

Ext.UpdateManager = function(el, forceNew){
    el = Ext.get(el);
    if(!forceNew && el.updateManager){
        return el.updateManager;
    }
    
    this.el = el;
    
    this.defaultUrl = null;
    
    this.addEvents({
        
        "beforeupdate": true,
        
        "update": true,
        
        "failure": true
    });
    var d = Ext.UpdateManager.defaults;
    
    this.sslBlankUrl = d.sslBlankUrl;
    
    this.disableCaching = d.disableCaching;
    
    this.indicatorText = d.indicatorText;
    
    this.showLoadIndicator = d.showLoadIndicator;
    
    this.timeout = d.timeout;
    
    
    this.loadScripts = d.loadScripts;
    
    
    this.transaction = null;
    
    
    this.autoRefreshProcId = null;
    
    this.refreshDelegate = this.refresh.createDelegate(this);
    
    this.updateDelegate = this.update.createDelegate(this);
    
    this.formUpdateDelegate = this.formUpdate.createDelegate(this);
    
    this.successDelegate = this.processSuccess.createDelegate(this);
    
    this.failureDelegate = this.processFailure.createDelegate(this);
     
     
    this.renderer = new Ext.UpdateManager.BasicRenderer();

    Ext.UpdateManager.superclass.constructor.call(this);
};

Ext.extend(Ext.UpdateManager, Ext.util.Observable, {
    
    getEl : function(){
        return this.el;
    },
    
    update : function(url, params, callback, discardUrl){
        if(this.fireEvent("beforeupdate", this.el, url, params) !== false){
            var method = this.method;
            if(typeof url == "object"){ // must be config object
                var cfg = url;
                url = cfg.url;
                params = params || cfg.params;
                callback = callback || cfg.callback;
                discardUrl = discardUrl || cfg.discardUrl;
                if(callback && cfg.scope){
                    callback = callback.createDelegate(cfg.scope);
                }
                if(typeof cfg.method != "undefined"){method = cfg.method;};
                if(typeof cfg.nocache != "undefined"){this.disableCaching = cfg.nocache;};
                if(typeof cfg.text != "undefined"){this.indicatorText = '<div class="loading-indicator">'+cfg.text+"</div>";};
                if(typeof cfg.scripts != "undefined"){this.loadScripts = cfg.scripts;};
                if(typeof cfg.timeout != "undefined"){this.timeout = cfg.timeout;};
            }
            this.showLoading();
            if(!discardUrl){
                this.defaultUrl = url;
            }
            if(typeof url == "function"){
                url = url.call(this);
            }
            if(typeof params == "function"){
                params = params();
            }
            if(params && typeof params != "string"){ // must be object
                var buf = [];
                for(var key in params){
                    if(typeof params[key] != "function"){
                        buf.push(encodeURIComponent(key), "=", encodeURIComponent(params[key]), "&");
                    }
                }
                delete buf[buf.length-1];
                params = buf.join("");
            }
            var cb = {
                success: this.successDelegate,
                failure: this.failureDelegate,
                timeout: (this.timeout*1000),
                argument: {"url": url, "form": null, "callback": callback, "params": params}
            };
            method = method || (params ? "POST" : "GET");
            if(method == "GET"){
                url = this.prepareUrl(url);
            }
            this.transaction = Ext.lib.Ajax.request(method, url, cb, params);
        }
    },
    
    
    formUpdate : function(form, url, reset, callback){
        if(this.fireEvent("beforeupdate", this.el, form, url) !== false){
            formEl = Ext.getDom(form);
            if(typeof url == "function"){
                url = url.call(this);
            }
            if(typeof params == "function"){
                params = params();
            }
            url = url || formEl.action;
            var cb = {
                success: this.successDelegate,
                failure: this.failureDelegate,
                timeout: (this.timeout*1000),
                argument: {"url": url, "form": formEl, "callback": callback, "reset": reset}
            };
            var isUpload = false;
            var enctype = formEl.getAttribute("enctype");
            if(enctype && enctype.toLowerCase() == "multipart/form-data"){
                isUpload = true;
                cb.upload = this.successDelegate;
            }
            this.transaction = Ext.lib.Ajax.formRequest(formEl, url, cb, null, isUpload, this.sslBlankUrl);
            this.showLoading.defer(1, this);
        }
    },
    
    
    refresh : function(callback){
        if(this.defaultUrl == null){
            return;
        }
        this.update(this.defaultUrl, null, callback, true);
    },
    
    
    startAutoRefresh : function(interval, url, params, callback, refreshNow){
        if(refreshNow){
            this.update(url || this.defaultUrl, params, callback, true);
        }
        if(this.autoRefreshProcId){
            clearInterval(this.autoRefreshProcId);
        }
        this.autoRefreshProcId = setInterval(this.update.createDelegate(this, [url || this.defaultUrl, params, callback, true]), interval*1000);
    },
    
    
     stopAutoRefresh : function(){
        if(this.autoRefreshProcId){
            clearInterval(this.autoRefreshProcId);
            delete this.autoRefreshProcId;
        }
    },

    isAutoRefreshing : function(){
       return this.autoRefreshProcId ? true : false;  
    },
    
    showLoading : function(){
        if(this.showLoadIndicator){
            this.el.update(this.indicatorText);
        }
    },
    
    
    prepareUrl : function(url){
        if(this.disableCaching){
            var append = "_dc=" + (new Date().getTime());
            if(url.indexOf("?") !== -1){
                url += "&" + append;
            }else{
                url += "?" + append;
            }
        }
        return url;
    },
    
    
    processSuccess : function(response){
        this.transaction = null;
        if(response.argument.form && response.argument.reset){
            try{ // put in try/catch since some older FF releases had problems with this
                response.argument.form.reset();
            }catch(e){}
        }
        if(this.loadScripts){
            this.renderer.render(this.el, response, this, 
                this.updateComplete.createDelegate(this, [response]));
        }else{
            this.renderer.render(this.el, response, this);
            this.updateComplete(response);
        }
    },
    
    updateComplete : function(response){
        this.fireEvent("update", this.el, response);
        if(typeof response.argument.callback == "function"){
            response.argument.callback(this.el, true, response);
        }
    },
    
    
    processFailure : function(response){
        this.transaction = null;
        this.fireEvent("failure", this.el, response);
        if(typeof response.argument.callback == "function"){
            response.argument.callback(this.el, false, response);
        }
    },
    
    
    setRenderer : function(renderer){
        this.renderer = renderer;
    },
    
    getRenderer : function(){
       return this.renderer;  
    },
    
    
    setDefaultUrl : function(defaultUrl){
        this.defaultUrl = defaultUrl;
    },
    
    
    abort : function(){
        if(this.transaction){
            Ext.lib.Ajax.abort(this.transaction);
        }
    },
    
    
    isUpdating : function(){
        if(this.transaction){
            return Ext.lib.Ajax.isCallInProgress(this.transaction);
        }
        return false;
    }
});


   Ext.UpdateManager.defaults = {
       
         timeout : 30,
         
         
        loadScripts : false,
         
        
        sslBlankUrl : (Ext.SSL_SECURE_URL || "javascript:false"),
        
        disableCaching : false,
        
        showLoadIndicator : true,
        
        indicatorText : '<div class="loading-indicator">Loading...</div>'
   };


Ext.UpdateManager.updateElement = function(el, url, params, options){
    var um = Ext.get(el, true).getUpdateManager();
    Ext.apply(um, options);
    um.update(url, params, options ? options.callback : null);
};
// alias for backwards compat
Ext.UpdateManager.update = Ext.UpdateManager.updateElement;
 
Ext.UpdateManager.BasicRenderer = function(){};

Ext.UpdateManager.BasicRenderer.prototype = {
    
     render : function(el, response, updateManager, callback){
        el.update(response.responseText, updateManager.loadScripts, callback);
    }
};


Ext.util.DelayedTask = function(fn, scope, args){
    var id = null, d, t;

    var call = function(){
        var now = new Date().getTime();
        if(now - t >= d){
            clearInterval(id);
            id = null;
            fn.apply(scope, args || []);
        }
    };
    
    this.delay = function(delay, newFn, newScope, newArgs){
        if(id && delay != d){
            this.cancel();
        }
        d = delay;
        t = new Date().getTime();
        fn = newFn || fn;
        scope = newScope || scope;
        args = newArgs || args;
        if(!id){
            id = setInterval(call, d);
        }
    };

    
    this.cancel = function(){
        if(id){
            clearInterval(id);
            id = null;
        }
    };
};
