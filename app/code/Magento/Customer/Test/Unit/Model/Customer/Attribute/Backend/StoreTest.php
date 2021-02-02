<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Customer\Attribute\Backend;

use Magento\Customer\Model\Customer\Attribute\Backend\Store;

class StoreTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Store
     */
    protected $testable;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManager;

    protected function setUp(): void
    {
        $storeManager = $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMock();
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $this->testable = new \Magento\Customer\Model\Customer\Attribute\Backend\Store($storeManager);
    }

    public function testBeforeSaveWithId()
    {
        $object = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $object->expects($this->once())->method('getId')->willReturn(1);
        /** @var \Magento\Framework\DataObject $object */

        $this->assertInstanceOf(
            \Magento\Customer\Model\Customer\Attribute\Backend\Store::class,
            $this->testable->beforeSave($object)
        );
    }

    public function testBeforeSave()
    {
        $storeId = 1;
        $storeName = 'store';
        $object = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'hasStoreId', 'setStoreId', 'hasData', 'setData', 'getStoreId'])
            ->getMock();

        $store = $this->getMockBuilder(
            \Magento\Framework\DataObject::class
        )->setMethods(['getId', 'getName'])->getMock();
        $store->expects($this->once())->method('getId')->willReturn($storeId);
        $store->expects($this->once())->method('getName')->willReturn($storeName);

        $this->storeManager->expects($this->exactly(2))
            ->method('getStore')
            ->willReturn($store);

        $object->expects($this->once())->method('getId')->willReturn(false);
        $object->expects($this->once())->method('hasStoreId')->willReturn(false);
        $object->expects($this->once())->method('setStoreId')->with($storeId)->willReturn(false);
        $object->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $object->expects($this->once())->method('hasData')->with('created_in')->willReturn(false);
        $object->expects($this->once())
            ->method('setData')
            ->with($this->logicalOr('created_in', $storeName))
            ->willReturnSelf();
        /** @var \Magento\Framework\DataObject $object */

        $this->assertInstanceOf(
            \Magento\Customer\Model\Customer\Attribute\Backend\Store::class,
            $this->testable->beforeSave($object)
        );
    }
}
