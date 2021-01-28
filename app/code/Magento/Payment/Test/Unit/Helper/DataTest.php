<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Helper;

use \Magento\Payment\Helper\Data;

use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Payment\Helper\Data */
    private $helper;

    /**  @var \PHPUnit\Framework\MockObject\MockObject */
    private $scopeConfig;

    /**  @var \PHPUnit\Framework\MockObject\MockObject */
    private $initialConfig;

    /**  @var \PHPUnit\Framework\MockObject\MockObject */
    private $methodFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $layoutMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $appEmulation;

    protected function setUp(): void
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = \Magento\Payment\Helper\Data::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $context = $arguments['context'];
        $this->scopeConfig = $context->getScopeConfig();
        $this->layoutMock = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $layoutFactoryMock = $arguments['layoutFactory'];
        $layoutFactoryMock->expects($this->once())->method('create')->willReturn($this->layoutMock);

        $this->methodFactory = $arguments['paymentMethodFactory'];
        $this->appEmulation = $arguments['appEmulation'];
        $this->initialConfig = $arguments['initialConfig'];

        $this->helper = $objectManagerHelper->getObject($className, $arguments);
    }

    public function testGetMethodInstance()
    {
        list($code, $class, $methodInstance) = ['method_code', 'method_class', 'method_instance'];

        $this->scopeConfig->expects(
            $this->once()
        )->method(
            'getValue'
        )->willReturn(
            
                $class
            
        );
        $this->methodFactory->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            $class
        )->willReturn(
            
                $methodInstance
            
        );

        $this->assertEquals($methodInstance, $this->helper->getMethodInstance($code));
    }

    /**
     */
    public function testGetMethodInstanceWithException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->willReturn(null);

        $this->helper->getMethodInstance('code');
    }

    /**
     * @param array $methodA
     * @param array $methodB
     *
     * @dataProvider getSortMethodsDataProvider
     */
    public function testSortMethods(array $methodA, array $methodB)
    {
        $this->initialConfig->expects($this->once())
            ->method('getData')
            ->willReturn(
                
                    [
                        \Magento\Payment\Helper\Data::XML_PATH_PAYMENT_METHODS => [
                            $methodA['code'] => $methodA['data'],
                            $methodB['code'] => $methodB['data'],
                            'empty' => [],

                        ]
                    ]
                
            );

        $this->scopeConfig->expects(new MethodInvokedAtIndex(0))
            ->method('getValue')
            ->with(sprintf('%s/%s/model', Data::XML_PATH_PAYMENT_METHODS, $methodA['code']))
            ->willReturn(\Magento\Payment\Model\Method\AbstractMethod::class);
        $this->scopeConfig->expects(new MethodInvokedAtIndex(1))
            ->method('getValue')
            ->with(
                sprintf('%s/%s/model', Data::XML_PATH_PAYMENT_METHODS, $methodB['code'])
            )
            ->willReturn(\Magento\Payment\Model\Method\AbstractMethod::class);
        $this->scopeConfig->expects(new MethodInvokedAtIndex(2))
            ->method('getValue')
            ->with(sprintf('%s/%s/model', Data::XML_PATH_PAYMENT_METHODS, 'empty'))
            ->willReturn(null);

        $methodInstanceMockA = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $methodInstanceMockA->expects($this->any())
            ->method('isAvailable')
            ->willReturn(true);
        $methodInstanceMockA->expects($this->any())
            ->method('getConfigData')
            ->with('sort_order', null)
            ->willReturn($methodA['data']['sort_order']);

        $methodInstanceMockB = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $methodInstanceMockB->expects($this->any())
            ->method('isAvailable')
            ->willReturn(true);
        $methodInstanceMockB->expects($this->any())
            ->method('getConfigData')
            ->with('sort_order', null)
            ->willReturn($methodB['data']['sort_order']);

        $this->methodFactory->expects($this->at(0))
            ->method('create')
            ->willReturn($methodInstanceMockA);

        $this->methodFactory->expects($this->at(1))
            ->method('create')
            ->willReturn($methodInstanceMockB);

        $sortedMethods = $this->helper->getStoreMethods();
        $this->assertTrue(
            array_shift($sortedMethods)->getConfigData('sort_order')
            < array_shift($sortedMethods)->getConfigData('sort_order')
        );
    }

    public function testGetMethodFormBlock()
    {
        list($blockType, $methodCode) = ['method_block_type', 'method_code'];

        $methodMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setMethod', 'toHtml'])
            ->getMock();

        $methodMock->expects($this->once())->method('getFormBlockType')->willReturn($blockType);
        $methodMock->expects($this->once())->method('getCode')->willReturn($methodCode);
        $layoutMock->expects($this->once())->method('createBlock')
            ->with($blockType, $methodCode)
            ->willReturn($blockMock);
        $blockMock->expects($this->once())->method('setMethod')->with($methodMock);

        $this->assertSame($blockMock, $this->helper->getMethodFormBlock($methodMock, $layoutMock));
    }

    public function testGetInfoBlock()
    {
        $blockType = 'method_block_type';

        $methodMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $infoMock = $this->getMockBuilder(\Magento\Payment\Model\Info::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setInfo', 'toHtml'])
            ->getMock();

        $infoMock->expects($this->once())->method('getMethodInstance')->willReturn($methodMock);
        $methodMock->expects($this->once())->method('getInfoBlockType')->willReturn($blockType);
        $this->layoutMock->expects($this->once())->method('createBlock')
            ->with($blockType)
            ->willReturn($blockMock);
        $blockMock->expects($this->once())->method('setInfo')->with($infoMock);

        $this->assertSame($blockMock, $this->helper->getInfoBlock($infoMock));
    }

    public function testGetInfoBlockHtml()
    {
        list($storeId, $blockHtml, $secureMode, $blockType) = [1, 'HTML MARKUP', true, 'method_block_type'];

        $methodMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();
        $infoMock = $this->getMockBuilder(\Magento\Payment\Model\Info::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $paymentBlockMock = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setArea', 'setIsSecureMode', 'getMethod', 'setStore', 'toHtml', 'setInfo'])
            ->getMock();

        $this->appEmulation->expects($this->once())->method('startEnvironmentEmulation')->with($storeId);
        $infoMock->expects($this->once())->method('getMethodInstance')->willReturn($methodMock);
        $methodMock->expects($this->once())->method('getInfoBlockType')->willReturn($blockType);
        $this->layoutMock->expects($this->once())->method('createBlock')
            ->with($blockType)
            ->willReturn($paymentBlockMock);
        $paymentBlockMock->expects($this->once())->method('setInfo')->with($infoMock);
        $paymentBlockMock->expects($this->once())->method('setArea')
            ->with(\Magento\Framework\App\Area::AREA_FRONTEND)
            ->willReturnSelf();
        $paymentBlockMock->expects($this->once())->method('setIsSecureMode')
            ->with($secureMode);
        $paymentBlockMock->expects($this->once())->method('getMethod')
            ->willReturn($methodMock);
        $methodMock->expects($this->once())->method('setStore')->with($storeId);
        $paymentBlockMock->expects($this->once())->method('toHtml')
            ->willReturn($blockHtml);
        $this->appEmulation->expects($this->once())->method('stopEnvironmentEmulation');

        $this->assertEquals($blockHtml, $this->helper->getInfoBlockHtml($infoMock, $storeId));
    }

    /**
     * @return array
     */
    public function getSortMethodsDataProvider()
    {
        return [
            [
                ['code' => 'methodA', 'data' => ['sort_order' => 0]],
                ['code' => 'methodB', 'data' => ['sort_order' => 1]]
            ],
            [
                ['code' => 'methodA', 'data' => ['sort_order' => 2]],
                ['code' => 'methodB', 'data' => ['sort_order' => 1]],
            ]
        ];
    }
}
