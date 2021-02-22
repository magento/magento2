<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

class FlatTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Flat
     */
    protected $_model;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManagerInterface;

    protected function setUp(): void
    {
        $this->_store = $this->createMock(\Magento\Store\Model\Store::class);

        $this->_storeManagerInterface = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->_storeManagerInterface->expects(
            $this->any()
        )->method(
            'getStore'
        )->willReturn(
            $this->_store
        );

        $this->_storeManagerInterface->expects(
            $this->any()
        )->method(
            'getDefaultStoreView'
        )->willReturn(
            $this->_store
        );

        $this->_model = new \Magento\Catalog\Model\ResourceModel\Product\Flat(
            $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class),
            $this->_storeManagerInterface,
            $this->createMock(\Magento\Catalog\Model\Config::class),
            $this->createMock(\Magento\Catalog\Model\Product\Attribute\DefaultAttributes::class)
        );
    }

    public function testSetIntStoreId()
    {
        $store = $this->_model->setStoreId(1);
        $storeId = $store->getStoreId();
        $this->assertEquals(1, $storeId);
    }

    public function testSetNotIntStoreId()
    {
        $this->_storeManagerInterface->expects($this->once())->method('getStore');

        $store = $this->_model->setStoreId('test');
        $storeId = $store->getStoreId();
        $this->assertEquals(0, $storeId);
    }
}
