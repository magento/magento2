<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Result;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

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
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
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
            ->will($this->returnValue($this->page));
        $this->assertSame($this->page, $this->pageFactory->create());
    }
}
