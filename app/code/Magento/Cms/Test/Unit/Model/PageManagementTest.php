<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\PageManagement;

/**
 * Test for Magento\Cms\Model\PageManagment
 */

class PageManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PageManagement
     */
    protected $pageManagment;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\Page
     */
    protected $page;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\ResourceModel\Page
     */
    protected $pageResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Api\Data\StoreInterface
     */
    protected $store;

    public function setUp()
    {
        $this->pageFactory = $this->getMockBuilder(\Magento\Cms\Model\PageFactory::class)
            ->disableOriginalConstructor(true)
            ->setMethods(['create'])
            ->getMock();

        $this->pageResource = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Page::class)
            ->disableOriginalConstructor(true)
            ->getMock();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor(true)
            ->getMock();

        $this->store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor(true)
            ->getMock();

        $this->page = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'getId'])
            ->getMock();

        $this->pageManagment = new PageManagement($this->pageFactory, $this->pageResource, $this->storeManager);
    }

    /**
     * Test for getByIdentifier method
     */
    public function testGetByIdentifier()
    {
        $identifier = 'home';
        $storeId = null;

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);

        $this->store->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->pageFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->page);

        $this->page->expects($this->once())
            ->method('setStoreId')
            ->willReturn($this->page);

        $this->page->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->pageResource->expects($this->once())
            ->method('load')
            ->with($this->page, $identifier)
            ->willReturn($this->page);

        $this->pageManagment->getByIdentifier($identifier, $storeId);
    }
}