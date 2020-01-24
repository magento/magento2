/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'escaper'
], function (escaper) {
    'use strict';

    /**
     * Dataprovider for escapeHtml tests
     *
     * @return {Object}
     */
    function escapeHtmlDataProvider() {
        return {
            'empty input': {
                data: '',
                expected: ''
            },
            'null input': {
                data: null,
                expected: ''
            },
            'empty with allowed tags': {
                data: '',
                expected: '',
                allowedTags: ['div']
            },
            'null allowed tags': {
                data: null,
                expected: '',
                allowedTags: ['div']
            },
            'malicious code not executed during processing of allowedTags': {
                data: '<div>foo<img src="bad" onerror="document.body.innerHTML = &quot;&quot;"></div>',
                expected: '<div>foo</div>',
                allowedTags: ['div']
            },
            'text with special characters': {
                data: '&<>"\'&amp;&lt;&gt;&quot;&#039;&#9;',
                expected: '&amp;&lt;&gt;"\'&amp;amp;&amp;lt;&amp;gt;&amp;quot;&amp;#039;&amp;#9;'
            },
            'text with special characters and allowed tag': {
                data: '&<br/>"\'&amp;&lt;&gt;&quot;&#039;&#9;',
                expected: '&amp;<br>"\'&amp;&lt;&gt;"\'\t',
                allowedTags: ['br']
            },
            'text with multiple allowed tags, includes self closing tag': {
                data: '<span>some text in tags<br /></span>',
                expected: '<span>some text in tags<br></span>',
                allowedTags: ['span', 'br']
            },
            'text with multiple allowed tags and allowed attribute in double quotes': {
                data: 'Only <span id="sku_max_allowed"><b>2</b></span> in stock',
                expected: 'Only <span id="sku_max_allowed"><b>2</b></span> in stock',
                allowedTags: ['span', 'b']
            },
            'text with multiple allowed tags and allowed attribute in single quotes': {
                data: 'Only <span id=\'sku_max_allowed\'><b>2</b></span> in stock',
                expected: 'Only <span id="sku_max_allowed"><b>2</b></span> in stock',
                allowedTags: ['span', 'b']
            },
            'text with multiple allowed tags with allowed attribute': {
                data: 'Only registered users can write reviews. Please <a href="%1">Sign in</a> or <a href="%2">' +
                    'create an account</a>',
                expected: 'Only registered users can write reviews. Please <a href="%1">Sign in</a> or ' +
                    '<a href="%2">create an account</a>',
                allowedTags: ['a']
            },
            'text with not allowed attribute in single quotes': {
                data: 'Only <span type=\'1\'><b>2</b></span> in stock',
                expected: 'Only <span><b>2</b></span> in stock',
                allowedTags: ['span', 'b']
            },
            'text with allowed and not allowed tags': {
                data: 'Only registered users can write reviews. Please <a href="%1">Sign in<span>three</span></a> ' +
                    'or <a href="%2"><span id="action">create an account</span></a>',
                expected: 'Only registered users can write reviews. Please <a href="%1">Sign inthree</a> or ' +
                    '<a href="%2">create an account</a>',
                allowedTags: ['a']
            },
            'text with allowed and not allowed tags, with allowed and not allowed attributes': {
                data: 'Some test <span style="fine">text in span tag</span> <strong>text in strong tag</strong> ' +
                    '<a type="some-type" href="http://domain.com/" style="bad" onclick="alert(1)">' +
                    'Click here</a><script>alert(1)' +
                    '</script>',
                expected: 'Some test <span style="fine">text in span tag</span> text in strong tag ' +
                    '<a href="http://domain.com/">' +
                    'Click here</a>alert(1)',
                allowedTags: ['a', 'span']
            },
            'text with html comment': {
                data: 'Only <span><b>2</b></span> in stock <!-- HTML COMMENT -->',
                expected: 'Only <span><b>2</b></span> in stock ',
                allowedTags: ['span', 'b']
            },
            'text with multiple comments': {
                data: 'Only <span><b>2</b></span> <!-- HTML COMMENT -->in stock <!-- HTML COMMENT -->',
                expected: 'Only <span><b>2</b></span> in stock ',
                allowedTags: ['span', 'b']
            },
            'text with multi-line html comment': {
                data: 'Only <span><b>2</b></span> in stock <!-- --!\n\n><img src=#>-->',
                expected: 'Only <span><b>2</b></span> in stock ',
                allowedTags: ['span', 'b']
            },
            'text with non ascii characters': {
                data: 'абвгдمثال幸福',
                expected: 'абвгдمثال幸福',
                allowedTags: []
            },
            'html and body tags': {
                data: '<html><body><span>String</span></body></html>',
                expected: '<span>String</span>',
                allowedTags: ['span']
            },
            'invalid tag': {
                data: '<some tag> some text',
                expected: ' some text',
                allowedTags: ['span']
            },
            'text with allowed script tag': {
                data: '<span><script>some text in tags</script></span>',
                expected: '<span>some text in tags</span>',
                allowedTags: ['span', 'script']
            },
            'text with invalid html': {
                data: '<spa>n id="id1">Some string</span>',
                expected: 'n id="id1"&gt;Some string',
                allowedTags: ['span']
            }
        };
    }

    describe('Magento_Security/js/escaper', function () {
        describe('escapeHtml', function () {
            var data = escapeHtmlDataProvider(),
                scenarioName,
                testData;

            for (scenarioName in data) { // eslint-disable-line guard-for-in
                testData = data[scenarioName];

                (function (dataSet) { // eslint-disable-line no-loop-func
                    /*
                     * Change to "it" instead of "xit" to run the tests.
                     * These are skipped due to PhantomJS not supporting DOMParser. The limitations of this ancient
                     * testing framework were not considered a limitation for a cross-browser solution of the escaper
                     * and the compromise was these tests won't be run automatically. Jasmine will generate
                     * _SpecRunner.html which can be loaded in a real browser to execute the tests when they need to be
                     * run. Regarding risk, this escaper doesn't have any external dependencies so it's very unlikely
                     * it will break unless modified directly. And in the event it's modified, run the tests in real
                     * supported browsers before merging.
                     */
                    xit(scenarioName, function () {
                        expect(escaper.escapeHtml(dataSet.data, dataSet.allowedTags)).toEqual(dataSet.expected);
                    });
                })(testData);
            }
        });
    });
});
