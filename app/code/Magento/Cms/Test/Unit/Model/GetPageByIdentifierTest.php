<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\GetPageByIdentifier;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Page as CmsModelResourcePage;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Cms\Model\GetPageByIdentifier
 */

class GetPageByIdentifierTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var GetPageByIdentifier
     */
    protected $getPageByIdentifierCommand;

    /**
     * @var MockObject|Page
     */
    protected $page;

    /**
     * @var MockObject|PageFactory
     */
    protected $pageFactory;

    /**
     * @var MockObject|CmsModelResourcePage
     */
    protected $pageResource;

    protected function setUp(): void
    {
        $this->pageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor(true)
            ->onlyMethods(['create'])
            ->getMock();

        $this->pageResource = $this->getMockBuilder(CmsModelResourcePage::class)
            ->disableOriginalConstructor(true)
            ->getMock();

        $this->page = $this->createPartialMockWithReflection(
            Page::class,
            ['setStoreId', 'getId']
        );

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
