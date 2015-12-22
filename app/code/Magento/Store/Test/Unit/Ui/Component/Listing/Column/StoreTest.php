<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var string
     */
    protected $name = 'anyname';

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->processorMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Processor')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->contextMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\ContextInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->uiComponentFactoryMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponentFactory')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->systemStoreMock = $this->getMockBuilder('Magento\Store\Model\System\Store')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->escaperMock = $this->getMockBuilder('Magento\Framework\Escaper')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($this->processorMock);
        $this->processorMock->expects($this->atLeastOnce())->method('register');
        $this->model = $objectManager->getObject(
            'Magento\Store\Ui\Component\Listing\Column\Store',
            [
                'context' => $this->contextMock,
                'uiComponent' => $this->uiComponentFactoryMock,
                'systemStore' =>  $this->systemStoreMock,
                'escaper' => $this->escaperMock,
                'components' => [],
                'data' => ['name' => $this->name]
            ]
        );
    }

    /**
     * @dataProvider prepareDataSourceDataProvider
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
