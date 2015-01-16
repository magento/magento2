<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Block\Adminhtml\Carrier\Tablerate;

class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflineShipping\Block\Adminhtml\Carrier\Tablerate\Grid
     */
    protected $model;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $context = $objectManager->getObject('Magento\Backend\Block\Template\Context', [
            'storeManager' => $this->storeManagerMock
        ]);

        $this->model = $objectManager->getObject('Magento\OfflineShipping\Block\Adminhtml\Carrier\Tablerate\Grid', [
            'context' => $context,
        ]);
    }

    public function testSetWebsiteId()
    {
        $websiteMock = $this->getMockBuilder('Magento\Store\Model\Website')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $this->model->setWebsiteId(1);
    }

    public function testGetWebsiteId()
    {
        $websiteId = 10;

        $websiteMock = $this->getMockBuilder('Magento\Store\Model\Website')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $this->assertEquals($websiteId, $this->model->getWebsiteId());
    }

    public function testSetConditionName()
    {
        $this->assertEquals($this->model, $this->model->setConditionName('someName'));
    }

    public function testGetConditionName()
    {
        $conditionName = 'someName';
        $this->assertEquals($conditionName, $this->model->setConditionName($conditionName)->getConditionName());
    }
}
