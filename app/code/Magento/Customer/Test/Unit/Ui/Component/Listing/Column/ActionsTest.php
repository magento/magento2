<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Ui\Component\Listing\Column\Actions;

class ActionsTest extends \PHPUnit\Framework\TestCase
{
    /** @var Actions */
    protected $component;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $uiComponentFactory;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    public function setup()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->createMock(\Magento\Framework\View\Element\UiComponentFactory::class);
        $this->urlBuilder = $this->getMockForAbstractClass(
            \Magento\Framework\UrlInterface::class,
            [],
            '',
            false
        );
        $this->component = new Actions(
            $this->context,
            $this->uiComponentFactory,
            $this->urlBuilder
        );
        $this->component->setData('name', 'name');
    }

    public function testPrepareDataSource()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'entity_id' => 1
                    ],
                ]
            ]
        ];
        $expectedDataSource = [
            'data' => [
                'items' => [
                    [
                        'entity_id' => 1,
                        'name' => [
                            'edit' => [
                                'href' => 'http://magento.com/customer/index/edit',
                                'label' => new \Magento\Framework\Phrase('Edit'),
                                'hidden' => false
                            ]
                        ]
                    ],
                ]
            ]
        ];

        $this->context->expects($this->once())
            ->method('getFilterParam')
            ->with('store_id')
            ->willReturn(null);
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with(
                'customer/*/edit',
                ['id' => 1, 'store' => null]
            )
            ->willReturn('http://magento.com/customer/index/edit');

        $dataSource = $this->component->prepareDataSource($dataSource);

        $this->assertEquals($expectedDataSource, $dataSource);
    }
}
