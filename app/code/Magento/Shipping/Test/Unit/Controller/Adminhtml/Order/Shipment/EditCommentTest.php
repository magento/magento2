<?php
/************************************************************************
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\EditComment;
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
