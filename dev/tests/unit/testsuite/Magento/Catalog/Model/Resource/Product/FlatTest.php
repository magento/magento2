<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Resource\Product;

class FlatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Flat
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

    public function setUp()
    {
        $this->_store = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);

        $this->_storeManagerInterface = $this->getMock('\Magento\Store\Model\StoreManagerInterface');

        $this->_storeManagerInterface->expects(
            $this->any()
        )->method(
            'getStore'
        )->will(
            $this->returnValue($this->_store)
        );

        $this->_storeManagerInterface->expects(
            $this->any()
        )->method(
            'getDefaultStoreView'
        )->will(
            $this->returnValue($this->_store)
        );

        $this->_model = new \Magento\Catalog\Model\Resource\Product\Flat(
            $this->getMock('Magento\Framework\App\Resource', [], [], '', false),
            $this->_storeManagerInterface,
            $this->getMock('Magento\Catalog\Model\Config', [], [], '', false)
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
