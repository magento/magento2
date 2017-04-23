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
use Magento\Framework\App\RequestInterface;

/**
 * Test class for \Magento\Widget\Controller\Adminhtml\Widget\LoadOptions
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonDataHelperMock;

    /**
     * @var \Magento\Framework\View\Element\BlockInterface
     */
    private $optionsMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layoutMock;

    /**
     * @var \Magento\Widget\Model\Widget\Instance
     */
    private $widgetInstanceMock;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistryMock;

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
        $this->jsonDataHelperMock = $this->getMockBuilder(\Magento\Framework\Json\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionsMock = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)
            ->setMethods(['setWidgetType', 'setWidgetValues'])
            ->getMockForAbstractClass();
        $this->layoutMock = $this->getMockForAbstractClass(\Magento\Framework\View\LayoutInterface::class);
        $this->widgetInstanceMock = $this->getMockBuilder(\Magento\Widget\Model\Widget\Instance::class)
            ->disableOriginalConstructor()
            ->setMethods(['setType', 'setWidgetParameters'])
            ->getMock();
        $this->coreRegistryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['register'])
            ->getMock();
    }
    
    public function testExecuteWithException()
    {
        $jsonResult = '{"error":true,"message":"Some error"}';
        $errorMessage = 'Some error';

        $this->jsonDataHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with(['error' => true, 'message' => $errorMessage])
            ->willReturn($jsonResult);

        $this->viewMock->expects($this->once())
            ->method('loadLayout')
            ->willThrowException(new LocalizedException(__($errorMessage)));

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with($jsonResult);

        $model = $this->objectManagerHelper->getObject(LoadOptions::class, [
            'context' => $this->contextMock,
            'jsonDataHelper' => $this->jsonDataHelperMock
        ]);

        $model->execute();
    }

    protected function setupExecuteTest()
    {
        $widgetType = 'Magento\SomeWidget';
        $widgetJsonParams = '{"widget_type":"Magento\\Widget","values":{"title":"&quot;Test&quot;", "":}}';
        $widgetArrayParams = [
            'widget_type' => $widgetType,
            'values' => [
                'title' => '&quot;Test&quot;'
            ],
        ];

        $this->jsonDataHelperMock->expects($this->once())
            ->method('jsonDecode')
            ->with($widgetJsonParams)
            ->willReturn($widgetArrayParams);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('widget')
            ->willReturn($widgetJsonParams);

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('wysiwyg_widget.options')
            ->willReturn($this->optionsMock);

        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($this->layoutMock);

        $instanceFactoryMock = $this->getMockBuilder(\Magento\Widget\Model\Widget\InstanceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $instanceFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->widgetInstanceMock));

        return $this->objectManagerHelper->getObject(LoadOptions::class, [
            'context' => $this->contextMock,
            'jsonDataHelper' => $this->jsonDataHelperMock,
            'widgetInstanceFactory' => $instanceFactoryMock,
            'coreRegistry' => $this->coreRegistryMock
        ]);
    }

    public function testExecuteShouldPrepareWidgetOptionsBlock()
    {
        $this->optionsMock->expects($this->once())
            ->method('setWidgetType')
            ->with('Magento\SomeWidget');

        $this->optionsMock->expects($this->once())
            ->method('setWidgetValues')
            ->with(['title' => '"Test"']);

        $model = $this->setupExecuteTest();

        $model->execute();
    }

    public function testExecuteShouldPrepareOptionsBlockBetweenLayoutLoadAndRender()
    {
        $callSequence = [];

        $this->optionsMock->expects($this->once())
            ->method('setWidgetType')
            ->will($this->returnCallback(function() use (&$callSequence) {
                $callSequence[] = 'setWidgetType';
            }));

        $this->optionsMock->expects($this->once())
            ->method('setWidgetValues')
            ->will($this->returnCallback(function() use (&$callSequence) {
                $callSequence[] = 'setWidgetValues';
            }));

        $this->viewMock->expects($this->once())
            ->method('loadLayout')
            ->will($this->returnCallback(function() use (&$callSequence) {
                $callSequence[] = 'loadLayout';
            }));

        $this->viewMock->expects($this->once())
            ->method('renderLayout')
            ->will($this->returnCallback(function() use (&$callSequence) {
                $callSequence[] = 'renderLayout';
            }));

        $model = $this->setupExecuteTest();

        $model->execute();

        $this->assertEquals('loadLayout', reset($callSequence));
        $this->assertEquals('renderLayout', end($callSequence));
    }

    public function testExecuteShouldRegisterWidgetInstanceWithRequestedOptions()
    {
        $this->widgetInstanceMock->expects($this->any())
            ->method('setType')
            ->with('Magento\SomeWidget');

        $this->widgetInstanceMock->expects($this->once())
            ->method('setWidgetParameters')
            ->with(['title' => '"Test"']);

        $this->coreRegistryMock->expects($this->once())
            ->method('register')
            ->with('current_widget_instance', $this->widgetInstanceMock);

        $model = $this->setupExecuteTest();

        $model->execute();
    }

    public function testExecuteShouldPrepareWidgetInstanceBetweenLayoutLoadAndRender()
    {
        $callSequence = [];

        $this->widgetInstanceMock->expects($this->any())
            ->method('setType')
            ->will($this->returnCallback(function() use (&$callSequence) {
                $callSequence[] = 'setType';
            }));

        $this->widgetInstanceMock->expects($this->once())
            ->method('setWidgetParameters')
            ->will($this->returnCallback(function() use (&$callSequence) {
                $callSequence[] = 'setWidgetParameters';
            }));

        $this->coreRegistryMock->expects($this->once())
            ->method('register')
            ->will($this->returnCallback(function() use (&$callSequence) {
                $callSequence[] = 'register';
            }));

        $this->viewMock->expects($this->once())
            ->method('loadLayout')
            ->will($this->returnCallback(function() use (&$callSequence) {
                $callSequence[] = 'loadLayout';
            }));

        $this->viewMock->expects($this->once())
            ->method('renderLayout')
            ->will($this->returnCallback(function() use (&$callSequence) {
                $callSequence[] = 'renderLayout';
            }));

        $model = $this->setupExecuteTest();

        $model->execute();

        $this->assertEquals('loadLayout', reset($callSequence));
        $this->assertEquals('renderLayout', end($callSequence));
    }
}
