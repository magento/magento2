<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\EditComment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Backend\Model\View\Result\Forward;

/**
 * Edit comment test feature
 */
class EditCommentTest extends TestCase
{
    /**
     * @var EditComment
     */
    protected $controller;

    /**
     * @var ForwardFactory|MockObject
     */
    protected $resultForwardFactory;

    /**
     * @var Forward|MockObject
     */
    protected $forward;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->resultForwardFactory = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->forward = $this->getMockBuilder(Forward::class)->disableOriginalConstructor()->getMock();
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->controller = $objectManager->getObject(
            EditComment::class,
            [
                'context' => $this->context,
                'resultForwardFactory' => $this->resultForwardFactory
            ]
        );
    }

    /**
     * Test execute
     *
     * @return void
     */
    public function testExecute()
    {
        $this->resultForwardFactory->expects($this->any())->method('create')->willReturn($this->forward);

        $this->forward->expects($this->any())
            ->method('forward')
            ->with('addComment')
            ->willReturnSelf();

        $this->assertInstanceOf(
            Forward::class,
            $this->controller->execute()
        );
    }
}
