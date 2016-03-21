/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
DecoratorTest = TestCase('DecoratorTest');
DecoratorTest.prototype.testDecoratorList = function () {
    /*:DOC += <ul id="list">
     <li>item1</li>
     <li>item2</li>
     <li>item3</li>
     <li>item4</li>
     </ul>
     */
    var list = $('#list');
    list.decorate('list');
    assertTrue($(list.find('li')[0]).hasClass('odd'));
    assertFalse($(list.find('li')[0]).hasClass('even'));
    assertTrue($(list.find('li')[1]).hasClass('even'));
    assertFalse($(list.find('li')[1]).hasClass('odd'));
    assertTrue($(list.find('li')[2]).hasClass('odd'));
    assertFalse($(list.find('li')[2]).hasClass('even'));
    assertTrue($(list.find('li')[3]).hasClass('even'));
    assertFalse($(list.find('li')[3]).hasClass('odd'));
    assertTrue($(list.find('li')[3]).hasClass('last'));
};

DecoratorTest.prototype.testDecoratorGeneral = function () {
    /*:DOC += <div id="foo">
     <div class="item even">item1</div>
     <div class="item odd">item2</div>
     <div class="item odd">item3</div>
     <div class="item even">item4</div>
     </div>
     */
    var itemClass = '.item';
    $(itemClass).decorate('generic');
    assertTrue($($(itemClass)[0]).hasClass('odd'));
    assertFalse($($(itemClass)[0]).hasClass('even'));
    assertTrue($($(itemClass)[0]).hasClass('first'));
    assertFalse($($(itemClass)[0]).hasClass('last'));

    assertFalse($($(itemClass)[1]).hasClass('odd'));
    assertTrue($($(itemClass)[1]).hasClass('even'));
    assertFalse($($(itemClass)[1]).hasClass('first'));
    assertFalse($($(itemClass)[1]).hasClass('last'));

    assertTrue($($(itemClass)[2]).hasClass('odd'));
    assertFalse($($(itemClass)[2]).hasClass('even'));
    assertFalse($($(itemClass)[2]).hasClass('first'));
    assertFalse($($(itemClass)[2]).hasClass('last'));

    assertFalse($($(itemClass)[3]).hasClass('odd'));
    assertTrue($($(itemClass)[3]).hasClass('even'));
    assertFalse($($(itemClass)[3]).hasClass('first'));
    assertTrue($($(itemClass)[3]).hasClass('last'));
};

DecoratorTest.prototype.testDecoratorTable = function (){
    /*:DOC += <table id="foo">
     <thead>
     <tr>
     <th>Month</th>
     <th>Savings</th>
     </tr>
     </thead>
     <tfoot>
     <tr>
     <td>Sum</td>
     <td>$180</td>
     </tr>
     </tfoot>
     <tbody>
     <tr>
     <td>January</td>
     <td>$100</td>
     </tr>
     <tr>
     <td>February</td>
     <td>$80</td>
     </tr>
     </tbody>
     </table>
     */
    var tableId = '#foo';
    $(tableId).decorate('table');
    assertTrue($(tableId).find('thead tr').hasClass('first'));
    assertTrue($(tableId).find('thead tr').hasClass('last'));
    assertFalse($(tableId).find('thead tr').hasClass('odd'));
    assertFalse($(tableId).find('thead tr').hasClass('even'));

    assertTrue($(tableId).find('tfoot tr').hasClass('first'));
    assertTrue($(tableId).find('tfoot tr').hasClass('last'));
    assertFalse($(tableId).find('tfoot tr').hasClass('odd'));
    assertFalse($(tableId).find('tfoot tr').hasClass('even'));

    assertFalse($(tableId).find('tfoot tr td').last().hasClass('first'));
    assertTrue($(tableId).find('tfoot tr td').last().hasClass('last'));
    assertFalse($(tableId).find('tfoot tr td').last().hasClass('odd'));
    assertFalse($(tableId).find('tfoot tr td').last().hasClass('even'));

    assertTrue($(tableId).find('tbody tr').first().hasClass('first'));
    assertTrue($(tableId).find('tbody tr').first().hasClass('odd'));
    assertFalse($(tableId).find('tbody tr').first().hasClass('last'));
    assertFalse($(tableId).find('tbody tr').first().hasClass('even'));
    assertFalse($(tableId).find('tbody tr').last().hasClass('first'));
    assertFalse($(tableId).find('tbody tr').last().hasClass('odd'));
    assertTrue($(tableId).find('tbody tr').last().hasClass('last'));
    assertTrue($(tableId).find('tbody tr').last().hasClass('even'));

    assertFalse($(tableId).find('tbody tr td').last().hasClass('first'));
    assertFalse($(tableId).find('tbody tr td').last().hasClass('odd'));
    assertTrue($(tableId).find('tbody tr td').last().hasClass('last'));
    assertFalse($(tableId).find('tbody tr td').last().hasClass('even'));
};

DecoratorTest.prototype.testDecoratorDataList = function () {
    /*:DOC += <dl id="data-list">
        <dt>item</dt>
        <dt>item</dt>
        <dd>item</dd>
        <dd>item</dd>
     </dl>
     */
    var listId = '#data-list';
    $(listId).decorate('dataList');
    assertTrue($(listId).find('dt').first().hasClass('odd'));
    assertFalse($(listId).find('dt').first().hasClass('even'));
    assertFalse($(listId).find('dt').first().hasClass('last'));

    assertTrue($(listId).find('dt').last().hasClass('even'));
    assertFalse($(listId).find('dt').last().hasClass('odd'));
    assertTrue($(listId).find('dt').last().hasClass('last'));

    assertTrue($(listId).find('dd').first().hasClass('odd'));
    assertFalse($(listId).find('dd').first().hasClass('even'));
    assertFalse($(listId).find('dd').first().hasClass('last'));

    assertTrue($(listId).find('dd').last().hasClass('even'));
    assertFalse($(listId).find('dd').last().hasClass('odd'));
    assertTrue($(listId).find('dd').last().hasClass('last'));
};
