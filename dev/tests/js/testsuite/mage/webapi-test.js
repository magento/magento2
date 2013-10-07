/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an e-mail
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    mage.js
 * @package     test
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
WebapiTest = TestCase('WebapiTest');

WebapiTest.prototype.testConstructorSuccess = function() {
    var successCallback = function(){};
    new $.mage.Webapi('baseUrl', {'timeout': 100, 'success': successCallback});
}

WebapiTest.prototype.testConstructorSuccessEmptyArgs = function() {
    new $.mage.Webapi('baseUrl');
}

WebapiTest.prototype.testConstructorInvalidBaseUrl = function() {
    expectAsserts(1);
    try {
        var invalidBaseUrl = 1;
        new $.mage.Webapi(invalidBaseUrl);
    } catch (e) {
        var expectedException = "String baseUrl parameter required";
        assertEquals("Invalid exception was thrown.", expectedException, e);
    }
}

WebapiTest.prototype.testCallInvalidMethod = function() {
    var Webapi = new $.mage.Webapi('baseUrl');
    expectAsserts(1);
    try {
        Webapi.call('resourceUri', 'INVALID_HTTP_METHOD');
    } catch (e) {
        var expectedException = "Method name is not valid: INVALID_HTTP_METHOD";
        assertEquals("Invalid exception was thrown.", expectedException, e);
    }
}

WebapiTest.prototype.testCallSuccessCallback = function() {
    // ensure that custom successCallback was executed
    expectAsserts(1);
    var successCallback = function(response) {
        assertObject("Response is expected to be an object", response);
    }
    var Webapi = new $.mage.Webapi('baseUrl', {'success': successCallback});
    $.ajax = function(settings) {
        settings.success({});
    };
    Webapi.call('products', 'GET');
}

WebapiTest.prototype.testCallErrorCallback = function() {
    // ensure that custom successCallback was executed
    expectAsserts(1);
    var errorCallback = function(response) {
        assertObject("Response is expected to be an object", response);
    }
    var Webapi = new $.mage.Webapi('baseUrl', {'error': errorCallback});
    $.ajax = function(settings) {
        settings.error({});
    };
    Webapi.call('products', 'GET');
}

WebapiTest.prototype.testCallProductGet = function() {
    var baseUri = 'baseUrl';
    var Webapi = new $.mage.Webapi(baseUri);
    var httpMethod = Webapi.method.get;
    var idObj = {id: 1};
    var productResourceUri = '/products/';
    var resourceVersion = 'v1';
    var expectedUri = baseUri + '/webapi/rest/' + resourceVersion + productResourceUri + '1';
    // ensure that $.ajax() was executed
    expectAsserts(3);
    $.ajax = function(settings) {
        assertEquals("URI for API call does not match with expected one.", expectedUri, settings.url);
        assertEquals("HTTP method for API call does not match with expected one.", httpMethod, settings.type);
        assertEquals("Data for API call does not match with expected one.", '1', settings.data);
    };
    Webapi.Product(resourceVersion).get(idObj);
};

WebapiTest.prototype.testCallProductCreate = function() {
    var baseUri = 'baseUrl';
    var Webapi = new $.mage.Webapi(baseUri);
    var httpMethod = Webapi.method.create;
    var productResourceUri = '/products/';
    var resourceVersion = 'v1';
    var expectedUri = baseUri + '/webapi/rest/' + resourceVersion + productResourceUri;
    productData = {
        "type_id": "simple",
        "attribute_set_id": 4,
        "sku": "1234567890",
        "weight": 1,
        "status": 1,
        "visibility": 4,
        "name": "Simple Product",
        "description": "Simple Description",
        "price": 99.95,
        "tax_class_id": 0
    };
    // ensure that $.ajax() was executed
    expectAsserts(3);
    $.ajax = function(settings) {
        assertEquals("URI for API call does not match with expected one.", expectedUri, settings.url);
        assertEquals("HTTP method for API call does not match with expected one.", httpMethod, settings.type);
        assertEquals("Data for API call does not match with expected one.", productData, settings.data);
    };
    Webapi.Product(resourceVersion).create(productData);
};

WebapiTest.prototype.testCallProductCreateInvalidVersion = function() {
    expectAsserts(1);
    var invalidVersion = 'invalidVersion';
    try {
        var Webapi = new $.mage.Webapi('BaseUrl');
        Webapi.Product(invalidVersion);
    } catch (e) {
        var expectedException = "Incorrect version format: " + invalidVersion;
        assertEquals("Invalid exception was thrown.", expectedException, e);
    }
};
