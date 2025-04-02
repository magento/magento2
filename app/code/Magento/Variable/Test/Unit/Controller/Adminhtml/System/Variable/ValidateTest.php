<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Variable\Test\Unit\Controller\Adminhtml\System\Variable;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Variable\Controller\Adminhtml\System\Variable\Validate;
use Magento\Variable\Model\Variable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateTest extends TestCase
{
    /**
     * @var Variable|MockObject
     */
    protected $variableMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var Validate|MockObject
     */
    protected $validateMock;

    /**
     * @var Json|MockObject
     */
    protected $resultJsonMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    protected function setUp(): void
    {
        $this->validateMock = $this->getMockBuilder(
            Validate::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->variableMock = $this->getMockBuilder(
            Variable::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->variableMock->expects($this->any())
            ->method('addData')
            ->willReturnSelf();

        $messagesMock = $this->getMockBuilder(Messages::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->onlyMethods(['getMessagesBlock'])
            ->addMethods(['initMessages'])
            ->getMockForAbstractClass();
        $this->layoutMock->expects($this->any())
            ->method('getMessagesBlock')
            ->willReturn($messagesMock);
        $layoutFactoryMock = $this->getMockBuilder(LayoutFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $layoutFactoryMock->expects($this->any())->method('create')->willReturn($this->layoutMock);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPost'])
            ->getMockForAbstractClass();
        $responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setError', 'setHtmlMessage'])
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')->willReturn($responseMock);
        $contextMock->expects($this->any())
            ->method('getMessageManager')->willReturn($this->messageManagerMock);

        $this->resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultJsonFactoryMock->expects($this->any())->method('create')->willReturn($this->resultJsonMock);

        $coreRegistryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validateMock = $this->getMockBuilder(
            Validate::class
        )->setConstructorArgs(
            [
                $contextMock,
                $coreRegistryMock,
                $this->getMockBuilder(ForwardFactory::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods(['create'])->getMock(),
                $resultJsonFactoryMock,
                $this->getMockBuilder(PageFactory::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods(['create'])->getMock(),
                $layoutFactoryMock,
            ]
        )->onlyMethods(['_initVariable'])->getMock();
        $this->validateMock->expects($this->any())
            ->method('_initVariable')
            ->willReturn($this->variableMock);
    }

    /**
     * @param mixed $result
     * @param string[] $responseArray
     * @dataProvider executeDataProvider
     */
    public function testExecute($result, $responseArray)
    {
        $getParamMap = [
            ['variable_id', null, null],
            ['store', 0, 0],
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')->willReturnMap($getParamMap);
        $this->requestMock->expects($this->any())
            ->method('getPost')->with('variable')->willReturn([]);

        $this->variableMock->expects($this->any())
            ->method('validate')
            ->willReturn($result);

        if ($result instanceof Phrase) {
            $this->messageManagerMock->expects($this->once())
                ->method('addError')
                ->with($result->getText());
            $this->layoutMock->expects($this->once())
                ->method('initMessages');
        }
        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($responseArray);

        $this->validateMock->execute();
    }

    /**
     * @return array
     */
    public static function executeDataProvider()
    {
        return [
            [ false, ['error' => false]],
            [ true, ['error' => false]],
            [ __('Variable Code must be unique.'), ['error' => true, 'html_message' => null]],
            [ __('Validation has failed.'), ['error' => true, 'html_message' => null]],
        ];
    }
}
