<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Result;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class PageFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $pageFactory;

    /** @var \Magento\Framework\View\Result\Page|\PHPUnit\Framework\MockObject\MockObject */
    protected $page;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->pageFactory = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Result\PageFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
        $this->page = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCreate()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\View\Result\Page::class)
            ->willReturn($this->page);
        $this->assertSame($this->page, $this->pageFactory->create());
    }
}
