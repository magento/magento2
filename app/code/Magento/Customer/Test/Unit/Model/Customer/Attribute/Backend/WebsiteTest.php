<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Customer\Attribute\Backend;

use Magento\Customer\Model\Customer\Attribute\Backend\Website;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class WebsiteTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Website
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
        $this->testable = new Website($storeManager);
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
            Website::class,
            $this->testable->beforeSave($object)
        );
    }

    public function testBeforeSave()
    {
        $websiteId = 1;
        $object = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasData', 'setData'])
            ->getMock();

        $store = $this->createPartialMockWithReflection(DataObject::class, ['getWebsiteId']);
        $store->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $object->expects($this->once())->method('hasData')->with('website_id')->willReturn(false);
        $object->expects($this->once())
            ->method('setData')
            ->with($this->logicalOr('website_id', $websiteId))
            ->willReturnSelf();
        /** @var DataObject $object */
        $this->assertInstanceOf(
            Website::class,
            $this->testable->beforeSave($object)
        );
    }
}
