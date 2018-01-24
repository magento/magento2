/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'wysiwygAdapter'
], function (wysiwyg) {
    'use strict';
    var decodedHtml = '<p><img src="{{media url=&quot;wysiwyg/banana.jpg&quot;}}" alt="" width="612" height="459"></p>',
        encodedHtml = '<p>' +
            '<img src="http://magento2.vagrant154/admin/cms/wysiwyg/directive/' +
            '___directive/e3ttZWRpYSB1cmw9Ind5c2l3eWcvYmFuYW5hLmpwZyJ9fQ%2C%2C" alt="" width="612" height="459">' +
            '</p>';

    wysiwyg.config = {'directives_url': 'http://magento2.vagrant154/admin/cms/wysiwyg/directive/'};

    describe('wysiwygAdapter', function () {
        it('encodes directives properly', function () {
            expect(wysiwyg.encodeDirectives(decodedHtml)).toEqual(encodedHtml);
        });

        it('decodes directives properly', function () {
            expect(wysiwyg.decodeDirectives(encodedHtml)).toEqual(decodedHtml);
        });
    });
});
