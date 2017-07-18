<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Ui\Component\Listing\Column;

class StoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Ui\Component\Listing\Column\Store
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiComponentFactoryMock;

    /**
     * @var \Magento\Store\Model\System\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $systemStoreMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var string
     */
    protected $name = 'anyname';

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->uiComponentFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->systemStoreMock = $this->getMockBuilder(\Magento\Store\Model\System\Store::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->model = $objectManager->getObject(
            \Magento\Store\Ui\Component\Listing\Column\Store::class,
            [
                'context' => $this->contextMock,
                'uiComponent' => $this->uiComponentFactoryMock,
                'systemStore' =>  $this->systemStoreMock,
                'escaper' => $this->escaperMock,
                'components' => [],
                'data' => ['name' => $this->name]
            ]
        );

        $this->injectMockedDependency($this->storeManagerMock, 'storeManager');
    }

    /**
     * Inject mocked object dependency
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $mockObject
     * @param string $propertyName
     * @return void
     *
     * @deprecated
     */
    private function injectMockedDependency($mockObject, $propertyName)
    {
        $reflection = new \ReflectionClass(get_class($this->model));
        $reflectionProperty = $reflection->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, $mockObject);
    }

    public function testPrepare()
    {
        $this->processorMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($this->processorMock);
        $this->processorMock->expects($this->atLeastOnce())->method('register');
        $this->storeManagerMock->expects($this->atLeastOnce())->method('isSingleStoreMode')->willReturn(false);
        $this->model->prepare();
        $config = $this->model->getDataByKey('config');
        $this->assertEmpty($config);
    }

    public function testPrepareWithSingleStore()
    {
        $this->processorMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($this->processorMock);
        $this->processorMock->expects($this->atLeastOnce())->method('register');
        $this->storeManagerMock->expects($this->atLeastOnce())->method('isSingleStoreMode')->willReturn(true);
        $this->model->prepare();
        $config = $this->model->getDataByKey('config');
        $this->assertNotEmpty($config);
        $this->assertArrayHasKey('componentDisabled', $config);
        $this->assertTrue($config['componentDisabled']);
    }

    /**
     * @dataProvider prepareDataSourceDataProvider
     * @deprecated
     */
    public function testPrepareDataSource($dataSource, $expectedResult)
    {
        $website = 'website';
        $group = 'group';
        $store = 'store';

        $storeStructure = [
            1 => [
                'value' => 1,
                'label' => $website,
                'children' => [
                    1 => [
                        'value' => 1,
                        'label' => $group,
                        'children' => [
                            1 => ['value' => 1, 'label' => $store]
                        ]
                    ]
                ]
            ]
        ];
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->willReturnMap([[$group, null, $group], [$store, null, $store]]);
        $this->systemStoreMock->expects($this->any())->method('getStoresStructure')->willReturn($storeStructure);
        $this->assertEquals($this->model->prepareDataSource($dataSource), $expectedResult);
    }

    /**
     * @deprecated
     */
    public function prepareDataSourceDataProvider()
    {
        $content = "website<br/>&nbsp;&nbsp;&nbsp;group<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;store<br/>";
        return [
            'withoutStore' => [
                'dataSource' => ['data' => ['items' => [['store_id' => null]]]],
                'expectedResult' => ['data' => ['items' => [['store_id' => null, $this->name => '']]]]
            ],
            'allStores' => [
                'dataSource' => ['data' => ['items' => [['store_id' => [0]]]]],
                'expectedResult' => ['data' => ['items' => [['store_id' => [0], $this->name => __('All Store Views')]]]]
            ],
            'Stores' => [
                'dataSource' => ['data' => ['items' => [['store_id' => [1]]]]],
                'expectedResult' => ['data' => ['items' => [['store_id' => [1], $this->name => $content]]]]
            ],

        ];
    }
}
