<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Ui\Component\Listing\Column\Actions;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** test for Listing Colummn
 */
class ActionsTest extends TestCase
{
    /** @var Actions */
    protected $component;

    /** @var ContextInterface|MockObject */
    protected $context;

    /** @var UiComponentFactory|MockObject */
    protected $uiComponentFactory;

    /** @var UrlInterface|MockObject */
    protected $urlBuilder;

    protected function setup(): void
    {
        $this->context = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->createMock(UiComponentFactory::class);
        $this->urlBuilder = $this->getMockForAbstractClass(
            UrlInterface::class,
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
                                'label' => new Phrase('Edit'),
                                'hidden' => false,
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
