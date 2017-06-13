<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Category;

use Magento\Catalog\Controller\Adminhtml\Category\Move;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;

/**
 * Class MoveTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MoveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var \Magento\Framework\View\LayoutFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Category\Move
     */
    private $moveController;

    /**
     * @var ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var ManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    public function setUp()
    {
        $this->resultJsonFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutFactoryMock = $this->getMockBuilder(\Magento\Framework\View\LayoutFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->getMock(\Magento\Backend\App\Action\Context::class, [], [], '', false);
        $this->loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);
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
        $this->request = $this
            ->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getPost'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->context->expects($this->once())->method('getRequest')->will($this->returnValue($this->request));
        $this->messageManager = $this->getMock(ManagerInterface::class);
        $this->context->expects($this->once())->method('getMessageManager')->willReturn($this->messageManager);
    }

    private function initObjectManager()
    {
        $this->objectManager = $this->getMock(ObjectManagerInterface::class);
        $moveController = new \ReflectionClass($this->moveController);
        $objectManagerProp = $moveController->getProperty('_objectManager');
        $objectManagerProp->setAccessible(true);
        $objectManagerProp->setValue($this->moveController, $this->objectManager);
    }

    public function testExecuteWithGenericException()
    {
        $messagesCollection = $this->getMockBuilder(\Magento\Framework\Message\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $messageBlock = $this->getMockBuilder(\Magento\Framework\View\Element\Messages::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock = $this->getMock(\Magento\Framework\View\LayoutInterface::class);
        $this->layoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($layoutMock);
        $layoutMock->expects($this->once())
            ->method('getMessagesBlock')
            ->willReturn($messageBlock);
        $wysiwigConfig = $this->getMockBuilder(\Magento\Cms\Model\Wysiwyg\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMock = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->expects($this->exactly(2))
            ->method('getPost')
            ->withConsecutive(['pid', false], ['aid', false])
            ->willReturnMap([['pid', false, 2], ['aid', false, 1]]);
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Catalog\Model\Category::class)
            ->willReturn($categoryMock);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->withConsecutive([Registry::class], [Registry::class], [\Magento\Cms\Model\Wysiwyg\Config::class])
            ->willReturnMap([[Registry::class, $registry], [\Magento\Cms\Model\Wysiwyg\Config::class, $wysiwigConfig]]);
        $categoryMock->expects($this->once())
            ->method('move')
            ->willThrowException(new \Exception(
                __('Some exception')
            ));
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with(__('There was a category move error.'));
        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(true)
            ->willReturn($messagesCollection);
        $messageBlock->expects($this->once())
            ->method('setMessages')
            ->with($messagesCollection);
        $resultJsonMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
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

    public function testExecuteWithLocaliedException()
    {
        $exceptionMessage = 'Sorry, but we can\'t find the new category you selected.';
        $messagesCollection = $this->getMockBuilder(\Magento\Framework\Message\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $messageBlock = $this->getMockBuilder(\Magento\Framework\View\Element\Messages::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock = $this->getMock(\Magento\Framework\View\LayoutInterface::class);
        $this->layoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($layoutMock);
        $layoutMock->expects($this->once())
            ->method('getMessagesBlock')
            ->willReturn($messageBlock);
        $wysiwigConfig = $this->getMockBuilder(\Magento\Cms\Model\Wysiwyg\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMock = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->expects($this->exactly(2))
            ->method('getPost')
            ->withConsecutive(['pid', false], ['aid', false])
            ->willReturnMap([['pid', false, 2], ['aid', false, 1]]);
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Catalog\Model\Category::class)
            ->willReturn($categoryMock);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->withConsecutive([Registry::class], [Registry::class], [\Magento\Cms\Model\Wysiwyg\Config::class])
            ->willReturnMap([[Registry::class, $registry], [\Magento\Cms\Model\Wysiwyg\Config::class, $wysiwigConfig]]);
        $this->messageManager->expects($this->once())
            ->method('addExceptionMessage');
        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(true)
            ->willReturn($messagesCollection);
        $messageBlock->expects($this->once())
            ->method('setMessages')
            ->with($messagesCollection);
        $resultJsonMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
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
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(
                __($exceptionMessage)
            ));
        $this->resultJsonFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);
        $this->assertTrue($this->moveController->execute());
    }

    public function testSuccessfullCategorySave()
    {
        $messagesCollection = $this->getMockBuilder(\Magento\Framework\Message\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $messageBlock = $this->getMockBuilder(\Magento\Framework\View\Element\Messages::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock = $this->getMock(\Magento\Framework\View\LayoutInterface::class);
        $this->layoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($layoutMock);
        $layoutMock->expects($this->once())
            ->method('getMessagesBlock')
            ->willReturn($messageBlock);
        $wysiwigConfig = $this->getMockBuilder(\Magento\Cms\Model\Wysiwyg\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMock = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->expects($this->exactly(2))
            ->method('getPost')
            ->withConsecutive(['pid', false], ['aid', false])
            ->willReturnMap([['pid', false, 2], ['aid', false, 1]]);
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Catalog\Model\Category::class)
            ->willReturn($categoryMock);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->withConsecutive([Registry::class], [Registry::class], [\Magento\Cms\Model\Wysiwyg\Config::class])
            ->willReturnMap([[Registry::class, $registry], [\Magento\Cms\Model\Wysiwyg\Config::class, $wysiwigConfig]]);
        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(true)
            ->willReturn($messagesCollection);
        $messageBlock->expects($this->once())
            ->method('setMessages')
            ->with($messagesCollection);
        $resultJsonMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
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
            ->method('addSuccess')
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
