<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Listing;

use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * Class ColumnsTest
 */
class ColumnsTest extends \PHPUnit_Framework_TestCase
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
        $processor = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Processor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())->method('getProcessor')->willReturn($processor);
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName()
    {
        $columns = $this->objectManager->getObject(
            'Magento\Ui\Component\Listing\Columns',
            [
                'context' => $this->contextMock,
                'data' => [
                    'js_config' => [
                        'extends' => 'test_config_extends'
                    ],
                    'config' => [
                        'dataType' => 'testType'
                    ]
                ]
            ]
        );

        $this->assertEquals($columns->getComponentName(), Columns::NAME);
    }

    /**
     * Run test prepare method
     *
     * @return void
     */
    public function testPrepare()
    {
        /** @var Column|\PHPUnit_Framework_MockObject_MockObject $componentMock */
        $columnMock = $this->getMock(
            'Magento\Ui\Component\Listing\Columns\Column',
            [],
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

        $data = [
            'name' => 'test_name',
            'js_config' => ['extends' => 'test_config_extends'],
            'config' => ['dataType' => 'test_type', 'sortable' => true]
        ];
        $saveUrl = 'module/controller/save';

        $this->contextMock->expects($this->once())
            ->method('getDataProvider')
            ->willReturn($dataProviderMock);
        $this->contextMock->expects($this->once())
            ->method('addComponentDefinition')
            ->with('columns', ['extends' => 'test_config_extends']);

        $dataProviderMock->expects($this->once())
            ->method('getFieldMetaInfo')
            ->with('test_name', 'test_column_name')
            ->willReturn(['test_meta' => 'test_meta_value']);

        $columnMock->expects($this->once())
            ->method('getName')
            ->willReturn('test_column_name');
        $columnMock->expects($this->once())
            ->method('getData')
            ->with('config')
            ->willReturn(['test_config_data' => 'test_config_value']);
        $columnMock->expects($this->once())
            ->method('setData')
            ->with('config', ['test_config_data' => 'test_config_value', 'test_meta' => 'test_meta_value']);

        /** @var Columns $columns */
        $columns = $this->objectManager->getObject(
            'Magento\Ui\Component\Listing\Columns',
            [
                'components' => [$columnMock],
                'context' => $this->contextMock,
                'data' => $data
            ]
        );
        $columns->setData(
            'config',
            [
                'test_config_data' => 'test_config_value',
                'editorConfig' => [
                    'clientConfig' => [
                        'saveUrl' => $saveUrl,
                    ]
                ]
            ]
        );
        $columns->prepare();
        $this->assertEquals(
            [
                'test_config_data' => 'test_config_value',
                'editorConfig' => [
                    'clientConfig' => [
                        'saveUrl' => $saveUrl,
                    ]
                ]
            ],
            $columns->getData('config')
        );
    }
}
