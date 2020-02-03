<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Controller\Adminhtml\Widget;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Widget\Controller\Adminhtml\Widget\LoadOptions;
use Magento\Widget\Helper\Conditions as ConditionsHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Widget\Controller\Adminhtml\Widget\LoadOptions
 */
class LoadOptionsTest extends TestCase
{
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
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Json|MockObject
     */
    private $jsonMock;

    /**
     * @var LoadOptions
     */
    private $loadOptions;

    /**
     * return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->viewMock = $this->getMockForAbstractClass(ViewInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(['representJson'])
            ->getMockForAbstractClass();
        $this->jsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())
            ->method('getView')
            ->willReturn($this->viewMock);
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->conditionsHelperMock = $this->getMockBuilder(ConditionsHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loadOptions = $this->objectManagerHelper->getObject(
            LoadOptions::class,
            [
                'context' => $this->contextMock,
                'conditionsHelper' => $this->conditionsHelperMock,
                'json' => $this->jsonMock
            ]
        );
    }

    /**
     * @return void
     */
    public function dtestExecuteWithException()
    {
        $jsonResult = '{"error":true,"message":"Some error"}';
        $errorMessage = 'Some error';

        $this->jsonMock->expects($this->once())
            ->method('serialize')
            ->with(['error' => true, 'message' => $errorMessage])
            ->willReturn($jsonResult);

        $this->viewMock->expects($this->once())
            ->method('loadLayout')
            ->willThrowException(new LocalizedException(__($errorMessage)));
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

        $this->jsonMock->expects($this->once())
            ->method('unserialize')
            ->with($widgetJsonParams)
            ->willReturn($widgetArrayParams);

        $this->viewMock->expects($this->once())
            ->method('loadLayout');
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('widget')
            ->willReturn($widgetJsonParams);

        /** @var BlockInterface|MockObject $blockMock */
        $blockMock = $this->getMockBuilder(BlockInterface::class)
            ->setMethods(['setWidgetType', 'setWidgetValues'])
            ->getMockForAbstractClass();
        $blockMock->expects($this->once())
            ->method('setWidgetType')
            ->with($widgetType)
            ->willReturnSelf();
        $blockMock->expects($this->once())
            ->method('setWidgetValues')
            ->with($resultWidgetArrayParams['values'])
            ->willReturnSelf();

        /** @var LayoutInterface|MockObject $layoutMock */
        $layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
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
