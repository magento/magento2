/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'wysiwygAdapter'
], function (wysiwygAdapter) {
    'use strict';

    var obj;

    beforeEach(function () {
        var Constr = function () {};

        Constr.prototype = wysiwygAdapter;

        obj = new Constr();
        obj.initialize('id', {
            'directives_url': 'http://example.com/admin/cms/wysiwyg/directive/'
        });
    });

    describe('wysiwygAdapter', function () {
        var decodedHtml = '<p>' +
            '<img src="{{media url=&quot;wysiwyg/banana.jpg&quot;}}" alt="" width="612" height="459"></p>',
            encodedHtml = '<p>' +
            '<img src="http://example.com/admin/cms/wysiwyg/directive/' +
            '___directive/e3ttZWRpYSB1cmw9Ind5c2l3eWcvYmFuYW5hLmpwZyJ9fQ%2C%2C" alt="" width="612" height="459">' +
            '</p>',
            encodedHtmlWithForwardSlashInImgSrc = encodedHtml.replace('%2C%2C', '%2C%2C/');

        describe('"encodeDirectives" method', function () {
            it('converts media directive img src to directive URL', function () {
                expect(obj.encodeDirectives(decodedHtml)).toEqual(encodedHtml);
            });
        });

        describe('"decodeDirectives" method', function () {
            it(
                'converts directive URL img src without a trailing forward slash ' +
                'to media url without a trailing forward slash',
                function () {
                    expect(obj.decodeDirectives(encodedHtml)).toEqual(decodedHtml);
                }
            );

            it('converts directive URL img src with a trailing forward slash ' +
                'to media url without a trailing forward slash',
                function () {
                    expect(obj.decodeDirectives(encodedHtmlWithForwardSlashInImgSrc)).toEqual(decodedHtml);
                }
            );
        });
    });
});
