<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Controller\Adminhtml\Widget;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Widget\Controller\Adminhtml\Widget\LoadOptions;
use Magento\Widget\Helper\Conditions as ConditionsHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Widget\Controller\Adminhtml\Widget\LoadOptions
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoadOptionsTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var ViewInterface|MockObject
     */
    private $viewMock;

    /**
     * @var ConditionsHelper|MockObject
     */
    private $conditionsHelperMock;

    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var LoadOptions
     */
    private $loadOptions;

    /**
     * return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->viewMock = $this->createMock(ViewInterface::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->responseMock = $this->createPartialMock(ResponseHttp::class, ['representJson']);
        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->expects($this->once())
            ->method('getView')
            ->willReturn($this->viewMock);
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->contextMock->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->conditionsHelperMock = $this->createMock(ConditionsHelper::class);

        $this->loadOptions = $this->objectManagerHelper->getObject(
            LoadOptions::class,
            ['context' => $this->contextMock]
        );
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->loadOptions,
            'conditionsHelper',
            $this->conditionsHelperMock
        );
    }

    /**
     * @return void
     */
    public function dtestExecuteWithException()
    {
        $jsonResult = '{"error":true,"message":"Some error"}';
        $errorMessage = 'Some error';

        /** @var Data|MockObject $jsonDataHelperMock */
        $jsonDataHelperMock = $this->createMock(Data::class);
        $jsonDataHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => $errorMessage])
            ->willReturn($jsonResult);

        $this->viewMock->expects($this->once())
            ->method('loadLayout')
            ->willThrowException(new LocalizedException(__($errorMessage)));
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(Data::class)
            ->willReturn($jsonDataHelperMock);
        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with($jsonResult)
            ->willReturnArgument(0);

        $this->loadOptions->execute();
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $widgetType = 'Magento\SomeWidget';
        $conditionsEncoded = 'encoded conditions';
        $conditionsDecoded = [
            'value' => 1,
            'operator' => '==',
            'attribute' => 'id',
        ];
        $widgetJsonParams = '{"widget_type":"Magento\\Widget","values":{"title":"&quot;Test&quot;", "":}}';
        $widgetArrayParams = [
            'widget_type' => $widgetType,
            'values' => [
                'title' => '&quot;Test&quot;',
                'conditions_encoded' => $conditionsEncoded,
            ],
        ];
        $resultWidgetArrayParams = [
            'widget_type' => $widgetType,
            'values' => [
                'title' => '"Test"',
                'conditions_encoded' => $conditionsEncoded,
                'conditions' => $conditionsDecoded,
            ],
        ];

        /** @var Data|MockObject $jsonDataHelperMock */
        $jsonDataHelperMock = $this->createMock(Data::class);
        $jsonDataHelperMock->expects($this->once())
            ->method('jsonDecode')
            ->with($widgetJsonParams)
            ->willReturn($widgetArrayParams);

        $this->viewMock->expects($this->once())
            ->method('loadLayout');
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('widget')
            ->willReturn($widgetJsonParams);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(Data::class)
            ->willReturn($jsonDataHelperMock);

        /** @var BlockInterface|MockObject $blockMock */
        $blockMock = $this->createPartialMockWithReflection(
            BlockInterface::class,
            ['setWidgetType', 'setWidgetValues', 'toHtml']
        );
        $blockMock->expects($this->once())
            ->method('setWidgetType')
            ->with($widgetType)
            ->willReturnSelf();
        $blockMock->expects($this->once())
            ->method('setWidgetValues')
            ->with($resultWidgetArrayParams['values'])
            ->willReturnSelf();

        /** @var LayoutInterface|MockObject $layoutMock */
        $layoutMock = $this->createMock(LayoutInterface::class);
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('wysiwyg_widget.options')
            ->willReturn($blockMock);

        $this->conditionsHelperMock->expects($this->once())
            ->method('decode')
            ->with($conditionsEncoded)
            ->willReturn($conditionsDecoded);
        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);
        $this->viewMock->expects($this->once())
            ->method('renderLayout');

        $this->loadOptions->execute();
    }
}
