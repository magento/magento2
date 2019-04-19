<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Ui\Component\Listing\Column;

use Magento\Store\Ui\Component\Listing\Column\StoreTitle;

/**
 * Class StoreTitleTest contains unit test for \Magento\Store\Ui\Component\Listing\Column\StoreTitle
 */
class StoreTitleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StoreTitle
     */
    private $component;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentFactory;

    public function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->createMock(\Magento\Framework\View\Element\UiComponentFactory::class);
        $this->component = new StoreTitle(
            $this->context,
            $this->uiComponentFactory
        );

        $this->component->setData('name', 'name');
    }

    /**
     * @covers \Magento\Store\Ui\Component\Listing\Column\StoreTitle::prepareDataSource
     */
    public function testPrepareDataSource()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'name' => 'Default Store View',
                        'store_id' => 1,
                        'store_code' => 'default',
                    ]
                ]
            ]
        ];

        $title = sprintf(
            '<a title="Edit Store View" href="%s">Default Store View</a><br />(Code: default)',
            'http://magento-2-1.dev/admin/system_store/editStore'
        );

        $expectedDataSource = [
            'data' => [
                'items' => [
                    [
                        'name' => $title,
                        'store_id' => 1,
                        'store_code' => 'default',
                    ]
                ]
            ]
        ];

        $this->context->expects($this->once())
            ->method('getUrl')
            ->with(
                'adminhtml/system_store/editStore',
                ['store_id' => 1]
            )
            ->willReturn('http://magento-2-1.dev/admin/system_store/editStore');

        $dataSource = $this->component->prepareDataSource($dataSource);
        $this->assertEquals($expectedDataSource, $dataSource);
    }
}
