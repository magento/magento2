<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Category\Move;
use Magento\Catalog\Model\Category;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MoveTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var LayoutFactory|MockObject
     */
    private $layoutFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var Move
     */
    private $moveController;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $objects = [
            [
                StoreManagerInterface::class,
                $this->createMock(StoreManagerInterface::class)
            ],
            [
                Registry::class,
                $this->createMock(Registry::class)
            ],
            [
                Config::class,
                $this->createMock(Config::class)
            ],
            [
                Session::class,
                $this->createMock(Session::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);
        $this->resultJsonFactoryMock = $this->createPartialMock(JsonFactory::class, ['create']);
        $this->layoutFactoryMock = $this->createPartialMock(LayoutFactory::class, ['create']);
        $this->context = $this->createMock(Context::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->fillContext();

        $this->moveController = new Move(
            $this->context,
            $this->resultJsonFactoryMock,
            $this->layoutFactoryMock,
            $this->loggerMock
        );
        $this->initObjectManager();
    }

    private function fillContext()
    {
        $this->request = $this->createPartialMockWithReflection(
            RequestInterface::class,
            [
                'getParam', 'getPost',
                'getModuleName', 'setModuleName', 'getActionName', 'setActionName',
                'getCookie', 'getDistroBaseUrl', 'getRequestUri', 'getScheme',
                'setParams', 'getParams', 'isSecure'
            ]
        );
        $this->request->method('getModuleName')->willReturn('catalog');
        $this->request->method('setModuleName')->willReturnSelf();
        $this->request->method('getActionName')->willReturn('move');
        $this->request->method('setActionName')->willReturnSelf();
        $this->request->method('getCookie')->willReturn(null);
        $this->request->method('getDistroBaseUrl')->willReturn('');
        $this->request->method('getRequestUri')->willReturn('/');
        $this->request->method('getScheme')->willReturn('http');
        $this->request->method('setParams')->willReturnSelf();
        $this->request->method('getParams')->willReturn([]);
        $this->request->method('isSecure')->willReturn(false);
        $this->context->expects($this->once())->method('getRequest')->willReturn($this->request);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->context->expects($this->once())->method('getMessageManager')->willReturn($this->messageManager);
    }

    private function initObjectManager()
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $moveController = new \ReflectionClass($this->moveController);
        $objectManagerProp = $moveController->getProperty('_objectManager');
        $objectManagerProp->setAccessible(true);
        $objectManagerProp->setValue($this->moveController, $this->objectManager);
    }

    public function testExecuteWithGenericException()
    {
        $messagesCollection = $this->createMock(Collection::class);
        $messageBlock = $this->createMock(Messages::class);
        $layoutMock = $this->createMock(LayoutInterface::class);
        $this->layoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($layoutMock);
        $layoutMock->expects($this->once())
            ->method('getMessagesBlock')
            ->willReturn($messageBlock);
        $wysiwygConfig = $this->createMock(Config::class);
        $registry = $this->createMock(Registry::class);
        $categoryMock = $this->createMock(Category::class);
        // Configure request parameters using willReturnMap
        $this->request->method('getParam')->willReturnMap([
            ['pid', false, 2],
            ['aid', false, 1],
        ]);
        $this->request->expects($this->exactly(2))->method('getPost')->willReturnMap([
            ['pid', false, 2],
            ['aid', false, 1],
        ]);
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(Category::class)
            ->willReturn($categoryMock);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([[Registry::class, $registry], [Config::class, $wysiwygConfig]]);
        $categoryMock->expects($this->once())
            ->method('move')
            ->willThrowException(new \Exception('Some exception'));
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('There was a category move error.'));
        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(true)
            ->willReturn($messagesCollection);
        $messageBlock->expects($this->once())
            ->method('setMessages')
            ->with($messagesCollection);
        $resultJsonMock = $this->createMock(Json::class);
        $messageBlock->expects($this->once())
            ->method('getGroupedHtml')
            ->willReturn('<body></body>');
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => '<body></body>',
                    'error' => true
                ]
            )
            ->willReturn(true);
        $this->resultJsonFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);
        $this->assertTrue($this->moveController->execute());
    }

    public function testExecuteWithLocalizedException()
    {
        $exceptionMessage = 'Sorry, but we can\'t find the new category you selected.';
        $messagesCollection = $this->createMock(Collection::class);
        $messageBlock = $this->createMock(Messages::class);
        $layoutMock = $this->createMock(LayoutInterface::class);
        $this->layoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($layoutMock);
        $layoutMock->expects($this->once())
            ->method('getMessagesBlock')
            ->willReturn($messageBlock);
        $wysiwygConfig = $this->createMock(Config::class);
        $registry = $this->createMock(Registry::class);
        $categoryMock = $this->createMock(Category::class);
        // Configure request parameters using willReturnMap
        $this->request->method('getParam')->willReturnMap([
            ['pid', false, 2],
            ['aid', false, 1],
        ]);
        $this->request->expects($this->exactly(2))->method('getPost')->willReturnMap([
            ['pid', false, 2],
            ['aid', false, 1],
        ]);
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(Category::class)
            ->willReturn($categoryMock);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([[Registry::class, $registry], [Config::class, $wysiwygConfig]]);
        $this->messageManager->expects($this->once())
            ->method('addExceptionMessage');
        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(true)
            ->willReturn($messagesCollection);
        $messageBlock->expects($this->once())
            ->method('setMessages')
            ->with($messagesCollection);
        $resultJsonMock = $this->createMock(Json::class);
        $messageBlock->expects($this->once())
            ->method('getGroupedHtml')
            ->willReturn('<body></body>');
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => '<body></body>',
                    'error' => true
                ]
            )
            ->willReturn(true);
        $categoryMock->expects($this->once())
            ->method('move')
            ->willThrowException(new LocalizedException(__($exceptionMessage)));
        $this->resultJsonFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);
        $this->assertTrue($this->moveController->execute());
    }

    public function testSuccessfulCategorySave()
    {
        $messagesCollection = $this->createMock(Collection::class);
        $messageBlock = $this->createMock(Messages::class);
        $layoutMock = $this->createMock(LayoutInterface::class);
        $this->layoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($layoutMock);
        $layoutMock->expects($this->once())
            ->method('getMessagesBlock')
            ->willReturn($messageBlock);
        $wysiwygConfig = $this->createMock(Config::class);
        $registry = $this->createMock(Registry::class);
        $categoryMock = $this->createMock(Category::class);
        // Configure request parameters using willReturnMap
        $this->request->method('getParam')->willReturnMap([
            ['pid', false, 2],
            ['aid', false, 1],
        ]);
        $this->request->expects($this->exactly(2))->method('getPost')->willReturnMap([
            ['pid', false, 2],
            ['aid', false, 1],
        ]);
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(Category::class)
            ->willReturn($categoryMock);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([[Registry::class, $registry], [Config::class, $wysiwygConfig]]);
        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(true)
            ->willReturn($messagesCollection);
        $messageBlock->expects($this->once())
            ->method('setMessages')
            ->with($messagesCollection);
        $resultJsonMock = $this->createMock(Json::class);
        $messageBlock->expects($this->once())
            ->method('getGroupedHtml')
            ->willReturn('<body></body>');
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => '<body></body>',
                    'error' => false
                ]
            )
            ->willReturn(true);
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You moved the category.'));
        $categoryMock->expects($this->once())
            ->method('move')
            ->with(2, 1);
        $this->resultJsonFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);
        $this->assertTrue($this->moveController->execute());
    }
}
