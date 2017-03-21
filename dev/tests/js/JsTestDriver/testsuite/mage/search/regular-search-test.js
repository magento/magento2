/**
 * @category    mage.js
 * @package     test
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
//Code to be tested for /app/code/Magento/Search/view/frontend/form-mini.js (_onSubmit)
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
