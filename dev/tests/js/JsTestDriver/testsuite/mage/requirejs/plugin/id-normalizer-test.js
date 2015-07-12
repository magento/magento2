/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint globalstrict: true*/

"use strict";

/*jshint undef: false, newcap: false*/
var IdNormalizerTest = TestCase('IdNormalizerTest');

IdNormalizerTest.prototype.setUp = function() {
    var defineArgs = jsunit.requirejsUtil.getDefineArgsInScript('lib/web/mage/requirejs/plugin/id-normalizer.js');
    assertNotUndefined('There expected to be a define() call', defineArgs);
    assertEquals('Wrong number of arguments in the define() call', 1, defineArgs.length);

    this.normalizer = defineArgs[0];
    assertObject(this.normalizer);
    assertFunction(this.normalizer.normalize);
    assertFunction(this.normalizer.load);
};

IdNormalizerTest.prototype.testNormalize = function () {
    var actual = this.normalizer.normalize('Magento_Catalog::foo/bar.js');
    assertEquals('Magento_Catalog/foo/bar.js', actual);
};

IdNormalizerTest.prototype.testLoad = function () {
    // Check that load() is just a proxy
    var modulePassed, onloadPassed;
    var parentRequire = function (moduleIn, onloadIn) {
        modulePassed = moduleIn;
        onloadPassed = onloadIn;
    };
    var onload = function (){};

    this.normalizer.load('module', parentRequire, onload);

    assertEquals('module', modulePassed);
    assertSame(onload, onloadPassed);
};

