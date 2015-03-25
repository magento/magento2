<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component;

use Magento\Ui\Component\Listing;
use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataSourceInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * Class ListingTest
 */
class ListingTest extends \PHPUnit_Framework_TestCase
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
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ContextInterface',
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
        /** @var Listing $listing */
        $listing = $this->objectManager->getObject(
            'Magento\Ui\Component\Listing',
            [
                'context' => $this->contextMock,
                'data' => []
            ]
        );

        $this->assertTrue($listing->getComponentName() === Listing::NAME);
    }

    /**
     * Run test prepare method
     *
     * @return void
     */
    public function testPrepare()
    {
        $buttons = [
            'button1' => 'button1',
            'button2' => 'button2'
        ];
        /** @var Listing $listing */
        $listing = $this->objectManager->getObject(
            'Magento\Ui\Component\Listing',
            [
                'context' => $this->contextMock,
                'data' => [
                    'js_config' => [
                        'extends' => 'test_config_extends',
                        'testData' => 'testValue',
                    ],
                    'buttons' => $buttons
                ]
            ]
        );

        $this->contextMock->expects($this->once())
            ->method('getNamespace')
            ->willReturn(Listing::NAME);
        $this->contextMock->expects($this->once())
            ->method('addComponentDefinition')
            ->with($listing->getComponentName(), ['testData' => 'testValue']);
        $this->contextMock->expects($this->once())
            ->method('addButtons')
            ->with($buttons, $listing);

        $listing->prepare();
    }

    /**
     * Run test getDataSourceData method
     *
     * @return void
     */
    public function testGetDataSourceData()
    {
        $result = [
            [
                'type' => 'test_component_name',
                'name' => 'test_name',
                'dataScope' => 'test_namespace',
                'config' => [
                    'data' => [
                        'items' => ['data']
                    ],
                    'totalCount' => 20,
                    'testConfig' => 'testConfigValue',
                    'params' => [
                        'namespace' => 'test_namespace'
                    ]
                ]
            ]
        ];

        /** @var DataSourceInterface|\PHPUnit_Framework_MockObject_MockObject $dataSourceMock */
        $dataSourceMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\DataSourceInterface',
            [],
            '',
            false
        );
        /** @var DataProviderInterface|\PHPUnit_Framework_MockObject_MockObject $dataProviderMock */
        $dataProviderMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface',
            [],
            '',
            false
        );
        /** @var Columns|\PHPUnit_Framework_MockObject_MockObject $columnsMock */
        $columnsMock = $this->getMock(
            'Magento\Ui\Component\Listing\Columns',
            [],
            [],
            '',
            false
        );
        /** @var Column|\PHPUnit_Framework_MockObject_MockObject $columnMock */
        $columnMock = $this->getMock(
            'Magento\Ui\Component\Listing\Columns\Column',
            [],
            [],
            '',
            false
        );

        /** @var Listing $listing */
        $listing = $this->objectManager->getObject(
            'Magento\Ui\Component\Listing',
            [
                'context' => $this->contextMock,
                'components' => [$dataSourceMock, $columnsMock],
                'data' => [
                    'js_config' => [
                        'extends' => 'test_config_extends',
                        'testData' => 'testValue',
                    ]
                ]
            ]
        );

        $columnsMock->expects($this->once())
            ->method('getChildComponents')
            ->willReturn([$columnMock]);

        $dataSourceMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($dataProviderMock);
        $dataProviderMock->expects($this->once())
            ->method('getData')
            ->willReturn(['items' => ['data']]);

        $columnMock->expects($this->once())
            ->method('prepareItems')
            ->with(['data']);

        $dataSourceMock->expects($this->once())
            ->method('getComponentName')
            ->willReturn('test_component_name');
        $dataSourceMock->expects($this->once())
            ->method('getName')
            ->willReturn('test_name');
        $dataSourceMock->expects($this->once())
            ->method('getContext')
            ->willReturn($this->contextMock);

        $this->contextMock->expects($this->any())
            ->method('getNamespace')
            ->willReturn('test_namespace');

        $dataProviderMock->expects($this->once())
            ->method('count')
            ->willReturn(20);

        $dataSourceMock->expects($this->once())
            ->method('getData')
            ->with('config')
            ->willReturn(['testConfig' => 'testConfigValue']);

        $this->assertEquals($listing->getDataSourceData(), $result);
    }
}
