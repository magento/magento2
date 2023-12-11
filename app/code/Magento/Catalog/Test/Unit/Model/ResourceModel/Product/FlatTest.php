<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product\Attribute\DefaultAttributes;
use Magento\Catalog\Model\ResourceModel\Product\Flat;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class FlatTest extends TestCase
{
    /**
     * @var Flat
     */
    protected $_model;

    /**
     * @var Store
     */
    protected $_store;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManagerInterface;

    protected function setUp(): void
    {
        $this->_store = $this->createMock(Store::class);

        $this->_storeManagerInterface = $this->getMockForAbstractClass(StoreManagerInterface::class);

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

        $this->_model = new Flat(
            $this->createMock(Context::class),
            $this->_storeManagerInterface,
            $this->createMock(Config::class),
            $this->createMock(DefaultAttributes::class)
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
