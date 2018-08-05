<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Ui\Component\Listing\Column;

use Magento\Store\Ui\Component\Listing\Column\GroupTitle;

/**
 * GroupTitleTest contains unit test for \Magento\Store\Ui\Component\Listing\Column\GroupTitle
 */
class GroupTitleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GroupTitle
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
        $this->component = new GroupTitle(
            $this->context,
            $this->uiComponentFactory
        );

        $this->component->setData('name', 'name');
    }

    /**
     * @covers \Magento\Store\Ui\Component\Listing\Column\GroupTitle::prepareDataSource
     */
    public function testPrepareDataSource()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'name' => 'Main Website Store',
                        'group_id' => 1,
                        'group_code' => 'main_website_store',
                    ]
                ]
            ]
        ];

        $title = sprintf(
            '<a title="Edit Store" href="%s">Main Website Store</a><br />(Code: main_website_store)',
            'http://magento-2-1.dev/admin/system_store/editGroup'
        );

        $expectedDataSource = [
            'data' => [
                'items' => [
                    [
                        'name' => $title,
                        'group_id' => 1,
                        'group_code' => 'main_website_store',
                    ]
                ]
            ]
        ];

        $this->context->expects($this->once())
            ->method('getUrl')
            ->with(
                'adminhtml/system_store/editGroup',
                ['group_id' => 1]
            )
            ->willReturn('http://magento-2-1.dev/admin/system_store/editGroup');

        $dataSource = $this->component->prepareDataSource($dataSource);
        $this->assertEquals($expectedDataSource, $dataSource);
    }
}
