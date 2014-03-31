<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogInventory\Model\Product\CopyConstructor;

class CatalogInventoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Product\CopyConstructor\CatalogInventory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_duplicateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stockItemMock;

    protected function setUp()
    {
        $this->_model = new \Magento\CatalogInventory\Model\Product\CopyConstructor\CatalogInventory();

        $this->_productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            array('__wakeup', 'getStockItem'),
            array(),
            '',
            false
        );

        $this->_duplicateMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            array('setStockData', 'unsStockItem', '__wakeup'),
            array(),
            '',
            false
        );

        $this->_stockItemMock = $this->getMock(
            'Magento\CatalogInventory\Model\Stock\Item',
            array(),
            array(),
            '',
            false
        );
    }

    public function testBuildWithoutCurrentProductStockItem()
    {
        $expectedData = array(
            'use_config_min_qty' => 1,
            'use_config_min_sale_qty' => 1,
            'use_config_max_sale_qty' => 1,
            'use_config_backorders' => 1,
            'use_config_notify_stock_qty' => 1
        );
        $this->_duplicateMock->expects($this->once())->method('unsStockItem');
        $this->_productMock->expects($this->once())->method('getStockItem')->will($this->returnValue(null));

        $this->_duplicateMock->expects($this->once())->method('setStockData')->with($expectedData);

        $this->_model->build($this->_productMock, $this->_duplicateMock);
    }

    public function testBuildWithCurrentProductStockItem()
    {
        $expectedData = array(
            'use_config_min_qty' => 1,
            'use_config_min_sale_qty' => 1,
            'use_config_max_sale_qty' => 1,
            'use_config_backorders' => 1,
            'use_config_notify_stock_qty' => 1,
            'use_config_enable_qty_inc' => 'use_config_enable_qty_inc',
            'enable_qty_increments' => 'enable_qty_increments',
            'use_config_qty_increments' => 'use_config_qty_increments',
            'qty_increments' => 'qty_increments'
        );
        $this->_duplicateMock->expects($this->once())->method('unsStockItem');
        $this->_productMock->expects(
            $this->once()
        )->method(
            'getStockItem'
        )->will(
            $this->returnValue($this->_stockItemMock)
        );

        $this->_stockItemMock->expects($this->any())->method('getData')->will($this->returnArgument(0));

        $this->_duplicateMock->expects($this->once())->method('setStockData')->with($expectedData);

        $this->_model->build($this->_productMock, $this->_duplicateMock);
    }
}
