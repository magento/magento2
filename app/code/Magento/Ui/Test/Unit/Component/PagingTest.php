<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component;

use Magento\Ui\Component\Paging;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * Class PagingTest
 */
class PagingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName()
    {
        $this->contextMock->expects($this->never())->method('getProcessor');
        /** @var Paging $listing */
        $paging = $this->objectManager->getObject(
            \Magento\Ui\Component\Paging::class,
            [
                'context' => $this->contextMock,
                'data' => []
            ]
        );

        $this->assertTrue($paging->getComponentName() === Paging::NAME);
    }

    /**
     * Run test prepare method
     *
     * @return void
     */
    public function testPrepare()
    {
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
        $resultData = [
            'js_config' => [
                'extends' => 'test_config_extends',
                'testData' => 'testValue'
            ],
            'config' => [
                'options' => [
                    'options1' => [
                        'value' => 20,
                        'label' => 'options1'
                    ],
                    'options2' => [
                        'value' => 40,
                        'label' => 'options2'
                    ],
                ],
                'pageSize' => 20,
                'current' => 2
            ]
        ];

        /** @var Paging $paging */
        $paging = $this->objectManager->getObject(
            \Magento\Ui\Component\Paging::class,
            [
                'context' => $this->contextMock,
                'data' => [
                    'js_config' => [
                        'extends' => 'test_config_extends',
                        'testData' => 'testValue',
                    ],
                    'config' => [
                        'options' => [
                            'options1' => [
                                'label' => 'options1',
                                'value' => '20'
                            ],
                            'options2' => [
                                'label' => 'options2',
                                'value' => '40'
                            ]
                        ],
                        'current' => 2,
                        'pageSize' => 20
                    ]
                ]
            ]
        );
        /** @var DataProviderInterface|\PHPUnit_Framework_MockObject_MockObject $dataProviderMock */
        $dataProviderMock = $this->getMockBuilder(DataProviderInterface::class)->getMockForAbstractClass();

        $this->contextMock->expects($this->once())
            ->method('getRequestParam')
            ->with('paging')
            ->willReturn(['pageSize' => 5, 'current' => 3]);
        $this->contextMock->expects($this->once())
            ->method('getDataProvider')
            ->willReturn($dataProviderMock);

        $dataProviderMock->expects($this->once())
            ->method('setLimit')
            ->with(3, 5);

        $this->contextMock->expects($this->once())
            ->method('addComponentDefinition')
            ->with($paging->getComponentName(), ['extends' => 'test_config_extends', 'testData' => 'testValue']);

        $paging->prepare();

        $this->assertEquals($paging->getData(), $resultData);
    }
}
