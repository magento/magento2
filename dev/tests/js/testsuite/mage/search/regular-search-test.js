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
 * obtain it through the world-wide-web, please send an email
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
//Code to be tested for /app/code/Magento/CatalogSearch/view/frontend/form-mini.js (_onSubmit)
function regularSearch() {
    if (this.document.getElementById('search').value === this.document.getElementById('search').placeholder || this.document.getElementById('search').value === '') {
        this.document.getElementById('search').placeholder = 'Please specify at least one search term';
        this.document.getElementById('search').value = this.document.getElementById('search').placeholder;
    }
}
//The test case
RegularSearchTest = TestCase("RegularSearchTest");
RegularSearchTest.prototype.setUp = function() {
    /*:DOC +=
     <div id='main'>
     <form id="search_mini_form" action="" method="get">
     <div>
     <label><span>Search</span></label>
     <div>
     <input id="search"
     type="text"
     name="q"
     value=""
     placeholder="Search entire store here..."/>
     </div>
     <div>
     <button id="submit" type="submit"
     title="Search">
     <span>Search</span>
     </button>
     </div>
     </form>
     </div>*/
};
RegularSearchTest.prototype.testRegularSearch = function(){
    //before
    var inputValue = document.getElementById('search');
    assertEquals("", inputValue.value);
    regularSearch();
    //after
    inputValue = document.getElementById('search');
    assertEquals("Please specify at least one search term", inputValue.value);
};
