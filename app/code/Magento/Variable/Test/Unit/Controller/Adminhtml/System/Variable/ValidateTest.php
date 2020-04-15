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
 * Variable validate test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateTest extends TestCase
{
    /**
     * @var Variable|MockObject
     */
    private $variableMock;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layoutMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var Validate|MockObject
     */
    private $validateMock;

    /**
     * @var Json|MockObject
     */
    private $resultJsonMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * Set Up
     */
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
            ->setMethods(['initMessages', 'getMessagesBlock'])
            ->getMockForAbstractClass();
        $this->layoutMock->expects($this->any())
            ->method('getMessagesBlock')
            ->willReturn($messagesMock);
        $layoutFactoryMock = $this->getMockBuilder(LayoutFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $layoutFactoryMock->expects($this->any())->method('create')->willReturn($this->layoutMock);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPost'])
            ->getMockForAbstractClass();
        $responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(['setError', 'setHtmlMessage'])
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getResponse')->will($this->returnValue($responseMock));
        $contextMock->expects($this->any())
            ->method('getMessageManager')->will($this->returnValue($this->messageManagerMock));

        $this->resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->setMethods(['create'])
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
                    ->disableOriginalConstructor()->setMethods(['create'])->getMock(),
                $resultJsonFactoryMock,
                $this->getMockBuilder(PageFactory::class)
                    ->disableOriginalConstructor()->setMethods(['create'])->getMock(),
                $layoutFactoryMock,
            ]
        )->setMethods(['_initVariable'])->getMock();
        $this->validateMock->expects($this->any())
            ->method('_initVariable')
            ->willReturn($this->variableMock);
    }

    /**
     * Test variable validation
     *
     * @param mixed $result
     * @param string[] $responseArray
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute($result, $responseArray): void
    {
        $getParamMap = [
            ['variable_id', null, null],
            ['store', 0, 0],
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')->willReturnMap($getParamMap);
        $this->requestMock->expects($this->any())
            ->method('getPost')->with('variable')->will($this->returnValue([]));

        $this->variableMock->expects($this->any())
            ->method('validate')
            ->willReturn($result);

        if ($result instanceof \Magento\Framework\Phrase) {
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
     * Validation cases data provider
     *
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            [ false, ['error' => false]],
            [ true, ['error' => false]],
            [ __('Variable Code must be unique.'), ['error' => true, 'html_message' => null]],
            [ __('Validation has failed.'), ['error' => true, 'html_message' => null]],
        ];
    }
}
