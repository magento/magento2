<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Test\Unit\Controller\Adminhtml\Widget;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Widget\Controller\Adminhtml\Widget\LoadOptions;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ViewInterface;
use Magento\Widget\Helper\Conditions as ConditionsHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Test class for \Magento\Widget\Controller\Adminhtml\Widget\LoadOptions
 */
class LoadOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $viewMock;

    /**
     * @var ConditionsHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionsHelperMock;

    /**
     * @var ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

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
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->viewMock = $this->getMockForAbstractClass(ViewInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(['representJson'])
            ->getMockForAbstractClass();
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
        $this->contextMock->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->conditionsHelperMock = $this->getMockBuilder(ConditionsHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        /** @var \Magento\Framework\Json\Helper\Data|\PHPUnit_Framework_MockObject_MockObject $jsonDataHelperMock */
        $jsonDataHelperMock = $this->getMockBuilder(\Magento\Framework\Json\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $jsonDataHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => $errorMessage])
            ->willReturn($jsonResult);

        $this->viewMock->expects($this->once())
            ->method('loadLayout')
            ->willThrowException(new LocalizedException(__($errorMessage)));
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\Json\Helper\Data::class)
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

        /** @var \Magento\Framework\Json\Helper\Data|\PHPUnit_Framework_MockObject_MockObject $jsonDataHelperMock */
        $jsonDataHelperMock = $this->getMockBuilder(\Magento\Framework\Json\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
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
            ->with(\Magento\Framework\Json\Helper\Data::class)
            ->willReturn($jsonDataHelperMock);

        /** @var \Magento\Framework\View\Element\BlockInterface|\PHPUnit_Framework_MockObject_MockObject $blockMock */
        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)
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

        /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject $layoutMock */
        $layoutMock = $this->getMockForAbstractClass(\Magento\Framework\View\LayoutInterface::class);
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
