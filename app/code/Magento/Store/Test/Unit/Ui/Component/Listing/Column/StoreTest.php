<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\System\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    /**
     * @var \Magento\Store\Ui\Component\Listing\Column\Store
     */
    protected $model;

    /**
     * @var Processor|MockObject
     */
    protected $processorMock;

    /**
     * @var ContextInterface|MockObject
     */
    protected $contextMock;

    /**
     * @var UiComponentFactory|MockObject
     */
    protected $uiComponentFactoryMock;

    /**
     * @var \Magento\Store\Model\System\Store|MockObject
     */
    protected $systemStoreMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var string
     */
    protected static $name = 'anyname';

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->uiComponentFactoryMock = $this->getMockBuilder(UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->systemStoreMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->model = $objectManager->getObject(
            \Magento\Store\Ui\Component\Listing\Column\Store::class,
            [
                'context' => $this->contextMock,
                'uiComponent' => $this->uiComponentFactoryMock,
                'systemStore' =>  $this->systemStoreMock,
                'escaper' => $this->escaperMock,
                'components' => [],
                'data' => ['name' => self::$name]
            ]
        );

        $this->injectMockedDependency($this->storeManagerMock, 'storeManager');
    }

    /**
     * Inject mocked object dependency
     *
     * @param MockObject $mockObject
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
        $this->processorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
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
        $this->processorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
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
    public static function prepareDataSourceDataProvider()
    {
        $content = "website<br/>&nbsp;&nbsp;&nbsp;group<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;store<br/>";
        return [
            'withoutStore' => [
                'dataSource' => ['data' => ['items' => [['store_id' => null]]]],
                'expectedResult' => ['data' => ['items' => [['store_id' => null, self::$name => '']]]]
            ],
            'allStores' => [
                'dataSource' => ['data' => ['items' => [['store_id' => [0]]]]],
                'expectedResult' => ['data' => ['items' => [['store_id' => [0], self::$name => __('All Store Views')]]]]
            ],
            'Stores' => [
                'dataSource' => ['data' => ['items' => [['store_id' => [1]]]]],
                'expectedResult' => ['data' => ['items' => [['store_id' => [1], self::$name => $content]]]]
            ],

        ];
    }
}
