<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Plugin;

class LayerTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\CatalogInventory\Helper\Stock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stockHelperMock;

    protected function setUp()
    {
        $this->_scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->_stockHelperMock = $this->createMock(\Magento\CatalogInventory\Helper\Stock::class);

        $this->_model = new \Magento\CatalogInventory\Model\Plugin\Layer(
            $this->_stockHelperMock,
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
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collectionMock */
        $collectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $this->_stockHelperMock->expects($this->never())->method('addIsInStockFilterToCollection');
        /** @var \Magento\Catalog\Model\Layer $subjectMock */
        $subjectMock = $this->createMock(\Magento\Catalog\Model\Layer::class);
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

        $collectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);

        $this->_stockHelperMock->expects(
            $this->once()
        )->method(
            'addIsInStockFilterToCollection'
        )->with(
            $collectionMock
        );

        $subjectMock = $this->createMock(\Magento\Catalog\Model\Layer::class);
        $this->_model->beforePrepareProductCollection($subjectMock, $collectionMock);
    }
}
