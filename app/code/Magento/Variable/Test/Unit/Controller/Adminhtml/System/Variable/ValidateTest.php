<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Variable\Test\Unit\Controller\Adminhtml\System\Variable;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Variable\Controller\Adminhtml\System\Variable\Validate;
use Magento\Variable\Model\Variable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Variable|MockObject
     */
    protected $variableMock;

    /**
     * @var Layout|MockObject
     */
    protected $layoutMock;

    /**
     * @var Http|MockObject
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
        $this->layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMessagesBlock', 'initMessages'])
            ->getMock();
        $this->layoutMock->expects($this->any())
            ->method('getMessagesBlock')
            ->willReturn($messagesMock);
        $layoutFactoryMock = $this->getMockBuilder(LayoutFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $layoutFactoryMock->expects($this->any())->method('create')->willReturn($this->layoutMock);

        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPost', 'getParam'])
            ->getMock();
        $responseMock = $this->createPartialMockWithReflection(
            ResponseHttp::class,
            ['setError', 'setHtmlMessage']
        );
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
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
     */
    #[DataProvider('executeDataProvider')]
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
