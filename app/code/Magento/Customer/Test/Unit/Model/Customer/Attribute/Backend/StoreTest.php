<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Customer\Attribute\Backend;

use Magento\Customer\Model\Customer\Attribute\Backend\Store;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class StoreTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Store
     */
    protected $testable;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    protected function setUp(): void
    {
        $storeManager = $this->storeManager = $this->createMock(StoreManagerInterface::class);
        /** @var StoreManagerInterface $storeManager */
        $this->testable = new Store($storeManager);
    }

    public function testBeforeSaveWithId()
    {
        $object = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getId']
        );

        $object->expects($this->once())->method('getId')->willReturn(1);
        /** @var DataObject $object */
        $this->assertInstanceOf(
            Store::class,
            $this->testable->beforeSave($object)
        );
    }

    public function testBeforeSave()
    {
        $storeId = 1;
        $storeName = 'store';
        $object = $this->createPartialMockWithReflection(
            DataObject::class,
            [
                'getId',
                'hasStoreId',
                'setStoreId',
                'getStoreId',
                'hasData',
                'setData'
            ]
        );

        $store = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getId', 'getName']
        );
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
        /** @var DataObject $object */
        $this->assertInstanceOf(
            Store::class,
            $this->testable->beforeSave($object)
        );
    }
}
