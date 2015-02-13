<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Customer\Attribute\Backend;

class WebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Website
     */
    protected $testable;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    public function setUp()
    {
        $storeManager = $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->getMock();
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $this->testable = new Website($storeManager);
    }

    public function testBeforeSaveWithId()
    {
        $object = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $object->expects($this->once())->method('getId')->will($this->returnValue(1));
        /** @var \Magento\Framework\Object $object */

        $this->assertInstanceOf(
            'Magento\Customer\Model\Customer\Attribute\Backend\Website',
            $this->testable->beforeSave($object)
        );
    }

    public function testBeforeSave()
    {
        $websiteId = 1;
        $object = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(['hasData', 'setData'])
            ->getMock();

        $store = $this->getMockBuilder('Magento\Framework\Object')->setMethods(['getWebsiteId'])->getMock();
        $store->expects($this->once())->method('getWebsiteId')->will($this->returnValue($websiteId));

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($store));

        $object->expects($this->once())->method('hasData')->with('website_id')->will($this->returnValue(false));
        $object->expects($this->once())
            ->method('setData')
            ->with($this->logicalOr('website_id', $websiteId))
            ->will($this->returnSelf());
        /** @var \Magento\Framework\Object $object */

        $this->assertInstanceOf(
            'Magento\Customer\Model\Customer\Attribute\Backend\Website',
            $this->testable->beforeSave($object)
        );
    }
}
