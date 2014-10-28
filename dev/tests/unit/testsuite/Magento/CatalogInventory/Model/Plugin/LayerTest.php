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
namespace Magento\CatalogInventory\Model\Plugin;

class LayerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Plugin\Layer
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Status|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stockStatusMock;

    public function setUp()
    {
        $this->_scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_stockStatusMock = $this->getMock(
            '\Magento\CatalogInventory\Model\Stock\Status',
            array(),
            array(),
            '',
            false
        );

        $this->_model = new \Magento\CatalogInventory\Model\Plugin\Layer(
            $this->_stockStatusMock,
            $this->_scopeConfigMock
        );
    }

    /**
     * Test add stock status to collection with disabled 'display out of stock' option
     */
    public function testAddStockStatusDisabledShow()
    {
        $this->_scopeConfigMock->expects(
            $this->once()
        )->method(
            'isSetFlag'
        )->with(
            'cataloginventory/options/show_out_of_stock'
        )->will(
            $this->returnValue(true)
        );
        $collectionMock = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\Collection',
            array(),
            array(),
            '',
            false
        );
        $this->_stockStatusMock->expects($this->never())->method('addIsInStockFilterToCollection');
        $subjectMock = $this->getMock('\Magento\Catalog\Model\Layer', array(), array(), '', false);
        $this->_model->beforePrepareProductCollection($subjectMock, $collectionMock);
    }

    /**
     *  Test add stock status to collection with 'display out of stock' option enabled
     */
    public function testAddStockStatusEnabledShow()
    {
        $this->_scopeConfigMock->expects(
            $this->once()
        )->method(
            'isSetFlag'
        )->with(
            'cataloginventory/options/show_out_of_stock'
        )->will(
            $this->returnValue(false)
        );

        $collectionMock = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\Collection',
            array(),
            array(),
            '',
            false
        );

        $this->_stockStatusMock->expects(
            $this->once()
        )->method(
            'addIsInStockFilterToCollection'
        )->with(
            $collectionMock
        );

        $subjectMock = $this->getMock('\Magento\Catalog\Model\Layer', array(), array(), '', false);
        $this->_model->beforePrepareProductCollection($subjectMock, $collectionMock);
    }
}
