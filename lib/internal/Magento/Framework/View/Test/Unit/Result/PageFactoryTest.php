<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Result;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PageFactoryTest extends TestCase
{
    /** @var PageFactory */
    protected $pageFactory;

    /** @var Page|MockObject */
    protected $page;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var ObjectManagerInterface|MockObject */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->pageFactory = $this->objectManagerHelper->getObject(
            PageFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
        $this->page = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCreate()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Page::class)
            ->willReturn($this->page);
        $this->assertSame($this->page, $this->pageFactory->create());
    }
}
