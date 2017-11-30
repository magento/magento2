<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\GetPageByIdentifier;

/**
 * Test for Magento\Cms\Model\GetPageByIdentifier
 */

class GetPageByIdentifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GetPageByIdentifier
     */
    protected $getPageByIdentifierCommand;

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

    public function setUp()
    {
        $this->pageFactory = $this->getMockBuilder(\Magento\Cms\Model\PageFactory::class)
            ->disableOriginalConstructor(true)
            ->setMethods(['create'])
            ->getMock();

        $this->pageResource = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Page::class)
            ->disableOriginalConstructor(true)
            ->getMock();

        $this->page = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'getId'])
            ->getMock();

        $this->getPageByIdentifierCommand = new GetPageByIdentifier($this->pageFactory, $this->pageResource);
    }

    /**
     * Test for getByIdentifier method
     */
    public function testGetByIdentifier()
    {
        $identifier = 'home';
        $storeId = 0;

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

        $this->getPageByIdentifierCommand->execute($identifier, $storeId);
    }
}