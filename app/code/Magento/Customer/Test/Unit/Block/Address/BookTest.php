<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Block\Address\Book;
use Magento\Customer\Block\Address\Grid;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Address\Config;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Title;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BookTest extends TestCase
{
    /**
     * @var Title|MockObject
     */
    private $title;

    /**
     * @var Book
     */
    private $block;

    protected function setUp(): void
    {
        $this->title = $this->createMock(Title::class);
        $pageConfig = $this->createMock(PageConfig::class);
        $pageConfig->method('getTitle')->willReturn($this->title);
        $context = $this->createMock(Context::class);
        $context->method('getPageConfig')->willReturn($pageConfig);

        $this->block = new Book(
            $context,
            null,
            $this->createMock(AddressRepositoryInterface::class),
            $this->createMock(CurrentCustomer::class),
            $this->createMock(Config::class),
            $this->createMock(Mapper::class),
            [],
            $this->createMock(Grid::class)
        );
    }

    public function testPrepareLayoutSetsDefaultTitleWhenNoneIsSet(): void
    {
        $this->title->method('getShortHeading')->willReturn(null);
        $this->title->expects($this->once())->method('set')->with('Address Book');

        $this->prepareLayout();
    }

    public function testPrepareLayoutKeepsTitleSetByLayout(): void
    {
        $this->title->method('getShortHeading')->willReturn('New Address Book Title');
        $this->title->expects($this->never())->method('set');

        $this->prepareLayout();
    }

    private function prepareLayout(): void
    {
        $method = new \ReflectionMethod($this->block, '_prepareLayout');
        $method->invoke($this->block);
    }
}
