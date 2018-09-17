<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Test\Unit\Controller\Adminhtml\System\Variable;

/**
 * Class ValidateTest
 */
class ValidateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Variable\Model\Variable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $variableMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Variable\Controller\Adminhtml\System\Variable\Validate | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validateMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    protected function setUp()
    {
        $this->validateMock = $this->getMockBuilder('Magento\Variable\Controller\Adminhtml\System\Variable\Validate')
            ->disableOriginalConstructor()
            ->getMock();

        $this->variableMock = $this->getMockBuilder('Magento\Variable\Model\Variable')
            ->disableOriginalConstructor()
            ->getMock();
        $this->variableMock->expects($this->any())
            ->method('addData')
            ->willReturnSelf();

        $messagesMock = $this->getMockBuilder('Magento\Framework\View\Element\Messages')
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->setMethods(['initMessages', 'getMessagesBlock'])
            ->getMockForAbstractClass();
        $this->layoutMock->expects($this->any())
            ->method('getMessagesBlock')
            ->willReturn($messagesMock);
        $layoutFactoryMock = $this->getMockBuilder('Magento\Framework\View\LayoutFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $layoutFactoryMock->expects($this->any())->method('create')->willReturn($this->layoutMock);

        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getPost'])
            ->getMockForAbstractClass();
        $responseMock = $this->getMockBuilder('Magento\Framework\App\ResponseInterface')
            ->setMethods(['setError', 'setHtmlMessage'])
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->getMockForAbstractClass();
        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getResponse')->will($this->returnValue($responseMock));
        $contextMock->expects($this->any())
            ->method('getMessageManager')->will($this->returnValue($this->messageManagerMock));

        $this->resultJsonMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();
        $resultJsonFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\Result\JsonFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultJsonFactoryMock->expects($this->any())->method('create')->willReturn($this->resultJsonMock);

        $coreRegistryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->validateMock = $this->getMockBuilder('Magento\Variable\Controller\Adminhtml\System\Variable\Validate')
            ->setConstructorArgs(
                [
                    $contextMock,
                    $coreRegistryMock,
                    $this->getMockBuilder('Magento\Backend\Model\View\Result\ForwardFactory')
                        ->disableOriginalConstructor()->setMethods(['create'])->getMock(),
                    $resultJsonFactoryMock,
                    $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
                        ->disableOriginalConstructor()->setMethods(['create'])->getMock(),
                    $layoutFactoryMock,
                ]
            )->setMethods(['_initVariable'])
            ->getMock();
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
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [ false, ['error' => false]],
            [ true, ['error' => false]],
            [ __('Variable Code must be unique.'), ['error' => true, 'html_message' => null]],
            [ __('Validation has failed.'), ['error' => true, 'html_message' => null]],
        ];
    }
}
