/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore'
], function (_) {
    'use strict';

    /* eslint-disable max-len */

    var schema = {
        blockContent: [
            'address', 'article', 'aside', 'blockquote', 'details', 'dialog', 'div', 'dl', 'fieldset',
            'figure', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hgroup', 'hr',
            'menu', 'nav', 'ol', 'p', 'pre', 'section', 'table', 'ul'
        ],
        phrasingContent: [
            '#comment', '#text', 'a', 'abbr', 'audio', 'b', 'bdi', 'bdo', 'br', 'button', 'canvas',
            'cite','code', 'command', 'datalist', 'del', 'dfn', 'em', 'embed', 'i', 'iframe', 'img',
            'input', 'ins', 'kbd', 'keygen', 'label', 'map', 'mark', 'meter', 'noscript', 'object',
            'output', 'picture', 'progress', 'q', 'ruby', 's', 'samp', 'script', 'select', 'small',
            'span', 'strong', 'sub', 'sup', 'textarea', 'time', 'u', 'var', 'video', 'wbr'
        ],
        blockElements: [
            'address', 'article', 'aside', 'blockquote', 'caption', 'center', 'datalist', 'dd', 'dir', 'div',
            'dl', 'dt', 'fieldset', 'figcaption', 'figure', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'header', 'hgroup', 'hr', 'isindex', 'li', 'menu', 'nav', 'noscript', 'ol', 'optgroup', 'option',
            'p', 'pre', 'section', 'select', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'ul'
        ],
        boolAttrs: [
            'autoplay', 'checked', 'compact', 'controls', 'declare', 'defer', 'disabled', 'ismap', 'loop',
            'multiple', 'nohref', 'noresize', 'noshade', 'nowrap', 'readonly', 'selected'
        ],
        shortEnded: [
            'area', 'base', 'basefont', 'br', 'col', 'embed', 'frame', 'hr', 'img', 'input', 'isindex',
            'link', 'meta', 'param', 'source', 'track', 'wbr'
        ],
        whiteSpace: [
            'audio', 'iframe', 'noscript', 'object', 'pre', 'script', 'style', 'textarea', 'video'
        ],
        selfClosing: [
            'colgroup', 'dd', 'dt', 'li', 'option', 'p', 'td', 'tfoot', 'th', 'thead', 'tr'
        ]
    };

    schema.flowContent = schema.blockContent.concat(schema.phrasingContent, ['style']);
    schema.nonEmpty = ['td', 'th', 'iframe', 'video', 'audio', 'object', 'script', 'i', 'em', 'span'].concat(schema.shortEnded);

    _.extend(schema, (function (phrasingContent, flowContent) {
        var validElements   = [],
            validChildren   = [],
            compiled        = {},
            globalAttrs,
            rawData;

        globalAttrs = [
            'id', 'dir', 'lang', 'class', 'style', 'title', 'hidden', 'onclick', 'onkeyup',
            'tabindex', 'dropzone', 'accesskey', 'draggable', 'translate', 'onmouseup',
            'onkeydown', 'spellcheck', 'ondblclick', 'onmouseout', 'onkeypress', 'contextmenu',
            'onmousedown', 'onmouseover', 'onmousemove', 'contenteditable'
        ];

        rawData = [
            ['html', 'manifest', 'head body'],
            ['head', '', 'base command link meta noscript script style title'],
            ['title hr noscript br'],
            ['base', 'href target'],
            ['link', 'href rel media hreflang type sizes hreflang'],
            ['meta', 'name http-equiv content charset'],
            ['style', 'media type scoped'],
            ['script', 'src async defer type charset'],
            ['body', 'onafterprint onbeforeprint onbeforeunload onblur onerror onfocus ' +
                'onhashchange onload onmessage onoffline ononline onpagehide onpageshow ' +
                'onpopstate onresize onscroll onstorage onunload background bgcolor text link vlink alink', flowContent
            ],
            ['caption', '', _.without(flowContent, 'table')],
            ['address dt dd div', '', flowContent],
            ['h1 h2 h3 h4 h5 h6 pre p abbr code var samp kbd sub sup i b u bdo span legend em strong small s cite dfn', '', phrasingContent],
            ['blockquote', 'cite', flowContent],
            ['ol', 'reversed start type', 'li'],
            ['ul', 'type compact', 'li'],
            ['li', 'value type', flowContent],
            ['dl', '', 'dt dd'],
            ['a', 'href target rel media hreflang type charset name rev shape coords download', phrasingContent],
            ['q', 'cite', phrasingContent],
            ['ins del', 'cite datetime', flowContent],
            ['img', 'src sizes srcset alt usemap ismap width height name longdesc align border hspace vspace'],
            ['iframe', 'src name width height longdesc frameborder marginwidth marginheight scrolling align sandbox seamless allowfullscreen', flowContent],
            ['embed', 'src type width height'],
            ['object', 'data type typemustmatch name usemap form width height declare classid code codebase codetype archive standby align border hspace vspace', flowContent.concat(['param'])],
            ['param', 'name value valuetype type'],
            ['map', 'name', flowContent.concat(['area'])],
            ['area', 'alt coords shape href target rel media hreflang type nohref'],
            ['table', 'border summary width frame rules cellspacing cellpadding align bgcolor', 'caption colgroup thead tfoot tbody tr col'],
            ['colgroup', 'span width align char charoff valign', 'col'],
            ['col', 'span'],
            ['tbody thead tfoot', 'align char charoff valign', 'tr'],
            ['tr', 'align char charoff valign bgcolor', 'td th'],
            ['td', 'colspan rowspan headers abbr axis scope align char charoff valign nowrap bgcolor width height', flowContent],
            ['th', 'colspan rowspan headers scope abbr axis align char charoff valign nowrap bgcolor width height accept', flowContent],
            ['form', 'accept-charset action autocomplete enctype method name novalidate target onsubmit onreset', flowContent],
            ['fieldset', 'disabled form name', flowContent.concat(['legend'])],
            ['label', 'form for', phrasingContent],
            ['input', 'accept alt autocomplete checked dirname disabled form formaction formenctype formmethod formnovalidate ' +
                'formtarget height list max maxlength min multiple name pattern readonly required size src step type value width usemap align'
            ],
            ['button', 'disabled form formaction formenctype formmethod formnovalidate formtarget name type value', phrasingContent],
            ['select', 'disabled form multiple name required size onfocus onblur onchange', 'option optgroup'],
            ['optgroup', 'disabled label', 'option'],
            ['option', 'disabled label selected value'],
            ['textarea', 'cols dirname disabled form maxlength name readonly required rows wrap'],
            ['menu', 'type label', flowContent.concat(['li'])],
            ['noscript', '', flowContent],
            ['wbr'],
            ['ruby', '', phrasingContent.concat(['rt', 'rp'])],
            ['figcaption', '', flowContent],
            ['mark rt rp summary bdi', '', phrasingContent],
            ['canvas', 'width height', flowContent],
            ['video', 'src crossorigin poster preload autoplay mediagroup loop muted controls width height buffered', flowContent.concat(['track', 'source'])],
            ['audio', 'src crossorigin preload autoplay mediagroup loop muted controls buffered volume', flowContent.concat(['track', 'source'])],
            ['picture', '', 'img source'],
            ['source', 'src srcset type media sizes'],
            ['track', 'kind src srclang label default'],
            ['datalist', '', phrasingContent.concat(['option'])],
            ['article section nav aside header footer', '', flowContent],
            ['hgroup', '', 'h1 h2 h3 h4 h5 h6'],
            ['figure', '', flowContent.concat(['figcaption'])],
            ['time', 'datetime', phrasingContent],
            ['dialog', 'open', flowContent],
            ['command', 'type label icon disabled checked radiogroup command'],
            ['output', 'for form name', phrasingContent],
            ['progress', 'value max', phrasingContent],
            ['meter', 'value min max low high optimum', phrasingContent],
            ['details', 'open', flowContent.concat(['summary'])],
            ['keygen', 'autofocus challenge disabled form keytype name'],
            ['script', 'language xml:space'],
            ['style', 'xml:space'],
            ['embed', 'align name hspace vspace'],
            ['br', 'clear'],
            ['applet', 'codebase archive code object alt name width height align hspace vspace'],
            ['font basefont', 'size color face'],
            ['h1 h2 h3 h4 h5 h6 div p legend caption', 'align'],
            ['ol dl menu dir', 'compact'],
            ['pre', 'width xml:space'],
            ['hr', 'align noshade size width'],
            ['isindex', 'prompt'],
            ['col', 'width align char charoff valign'],
            ['input button select textarea', 'autofocus'],
            ['input textarea', 'placeholder onselect onchange onfocus onblur'],
            ['link script img', 'crossorigin']
        ];

        rawData.forEach(function (data) {
            var nodes       = data[0].split(' '),
                attributes  = data[1] || [],
                children    = data[2] || [],
                ni          = nodes.length,
                nodeName,
                schemaData;

            if (typeof attributes === 'string') {
                attributes = attributes.split(' ');
            }

            if (typeof children === 'string') {
                children = children.split(' ');
            }

            while (ni--) {
                nodeName    = nodes[ni];
                schemaData  = compiled[nodeName] || {};

                compiled[nodeName] = {
                    attributes: _.union(schemaData.attributes, globalAttrs, attributes),
                    children: _.union(schemaData.children, children)
                };
            }
        });

        ['a', 'dfn', 'form', 'meter', 'progress'].forEach(function (nodeName) {
            var node = compiled[nodeName];

            node.children = _.without(node.children, nodeName);
        });

        _.each(compiled, function (node, nodeName) {
            var filteredAttributes = [];

            _.each(node.attributes, function (attribute) { //eslint-disable-line max-nested-callbacks
                // Disallowing usage of 'on*' attributes.
                if (!/^on/.test(attribute)) {
                    filteredAttributes.push(attribute);
                }
            });

            node.attributes = filteredAttributes;

            validElements.push(nodeName + '[' + node.attributes.join('|') + ']');
            validChildren.push(nodeName + '[' + node.children.join('|') + ']');
        });

        return {
            nodes: compiled,
            validElements: validElements,
            validChildren: validChildren
        };
    })(schema.phrasingContent, schema.flowContent));

    return schema;
});
