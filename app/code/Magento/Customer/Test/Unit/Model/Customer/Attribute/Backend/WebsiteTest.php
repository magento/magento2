<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Customer\Attribute\Backend;

use Magento\Customer\Model\Customer\Attribute\Backend\Website;

class WebsiteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Website
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
        $this->testable = new \Magento\Customer\Model\Customer\Attribute\Backend\Website($storeManager);
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
            \Magento\Customer\Model\Customer\Attribute\Backend\Website::class,
            $this->testable->beforeSave($object)
        );
    }

    public function testBeforeSave()
    {
        $websiteId = 1;
        $object = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasData', 'setData'])
            ->getMock();

        $store = $this->getMockBuilder(\Magento\Framework\DataObject::class)->setMethods(['getWebsiteId'])->getMock();
        $store->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $object->expects($this->once())->method('hasData')->with('website_id')->willReturn(false);
        $object->expects($this->once())
            ->method('setData')
            ->with($this->logicalOr('website_id', $websiteId))
            ->willReturnSelf();
        /** @var \Magento\Framework\DataObject $object */

        $this->assertInstanceOf(
            \Magento\Customer\Model\Customer\Attribute\Backend\Website::class,
            $this->testable->beforeSave($object)
        );
    }
}
