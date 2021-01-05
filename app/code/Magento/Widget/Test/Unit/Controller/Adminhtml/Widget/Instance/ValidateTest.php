<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Controller\Adminhtml\Widget\Instance;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use Magento\Widget\Controller\Adminhtml\Widget\Instance\Validate;
use Magento\Widget\Model\Widget\Instance;
use Magento\Widget\Model\Widget\InstanceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Widget\Controller\Adminhtml\Widget\Instance\Validate.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateTest extends TestCase
{
    private $errorMessage = 'We cannot create the widget instance because it is missing required information.';

    /**
     * @var Validate
     */
    private $model;

    /**
     * @var Layout|MockObject
     */
    private $layout;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var MockObject
     */
    private $responseMock;

    /**
     * @var MockObject
     */
    private $widgetMock;

    /**
     * @var Messages|MockObject
     */
    private $messagesBlock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $viewMock = $this->createMock(ViewInterface::class);
        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['initMessages'])
            ->getMockForAbstractClass();
        $this->messagesBlock = $this->createMock(Messages::class);
        $layoutMock->method('getMessagesBlock')->willReturn($this->messagesBlock);
        $viewMock->method('getLayout')->willReturn($layoutMock);
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['representJson'])
            ->getMockForAbstractClass();

        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($request);
        $context->method('getMessageManager')->willReturn($this->messageManagerMock);
        $context->method('getView')->willReturn($viewMock);
        $context->method('getResponse')->willReturn($this->responseMock);

        $this->widgetMock = $this->getMockBuilder(Instance::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setType', 'setCode', 'getType'])
            ->addMethods(['setThemeId', 'getThemeId'])
            ->getMock();
        $this->widgetMock->method('setType')->willReturnSelf();
        $this->widgetMock->method('setCode')->willReturnSelf();
        $this->widgetMock->method('setThemeId')->willReturnSelf();
        $widgetFactoryMock = $this->createMock(InstanceFactory::class);
        $widgetFactoryMock->method('create')->willReturn($this->widgetMock);

        $this->model = $objectManager->getObject(
            Validate::class,
            [
                'widgetFactory' => $widgetFactoryMock,
                'context' => $context,
                'layout' => $this->layout
            ]
        );
    }

    /**
     * Test execute
     *
     * @return void
     */
    public function testExecute(): void
    {
        $this->widgetMock->expects($this->once())
            ->method('getThemeId')
            ->willReturn(777);
        $this->widgetMock->expects($this->once())
            ->method('getType')
            ->willReturn('some type');

        $this->messageManagerMock->expects($this->never())
            ->method('addErrorMessage')
            ->with($this->errorMessage);
        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with(json_encode(['error' => false]));

        $this->model->execute();
    }

    /**
     * Test execute with Phrase object
     *
     * @return void
     */
    public function testExecutePhraseObject(): void
    {
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($this->errorMessage);
        $this->messagesBlock->expects($this->once())
            ->method('getGroupedHtml')
            ->willReturn($this->errorMessage);
        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with(json_encode(['error' => true, 'html_message' => $this->errorMessage]));

        $this->model->execute();
    }
}
