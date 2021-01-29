<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Ui\Component\Listing\Column;

use Magento\AsynchronousOperations\Model\BulkSummary;
use Magento\Framework\Bulk\BulkSummaryInterface;

class NotificationDismissActionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $uiComponentFactory;

    /**
     * @var \Magento\AsynchronousOperations\Ui\Component\Listing\Column\NotificationDismissActions
     */
    private $actionColumn;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(\Magento\Framework\View\Element\UiComponent\ContextInterface::class);
        $this->uiComponentFactory = $this->createMock(\Magento\Framework\View\Element\UiComponentFactory::class);
        $processor = $this->createPartialMock(
            \Magento\Framework\View\Element\UiComponent\Processor::class,
            ['getProcessor']
        );
        $this->context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->actionColumn = $objectManager->getObject(
            \Magento\AsynchronousOperations\Ui\Component\Listing\Column\NotificationDismissActions::class,
            [
                'context' => $this->context,
                'uiComponentFactory' => $this->uiComponentFactory,
                'components' => [],
                'data' => ['name' => 'actions']
            ]
        );
    }

    public function testPrepareDataSource()
    {
        $testData['data']['items'] = [
            [
                'key' => 'value',
            ],
            [
                BulkSummary::BULK_ID => 'uuid-1',
                'status' => BulkSummaryInterface::FINISHED_SUCCESSFULLY,
            ],
            [
                'status' => BulkSummaryInterface::IN_PROGRESS,
            ],
        ];
        $expectedResult['data']['items'] = [
            [
                'key' => 'value',
            ],
            [
                BulkSummary::BULK_ID => 'uuid-1',
                'status' => BulkSummaryInterface::FINISHED_SUCCESSFULLY,
                'actions' => [
                    'dismiss' => [
                        'href' => '#',
                        'label' => __('Dismiss'),
                        'callback' => [
                            [
                                'provider' => 'ns = notification_area, index = columns',
                                'target' => 'dismiss',
                                'params' => [
                                    0 => 'uuid-1',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'status' => BulkSummaryInterface::IN_PROGRESS,
            ],
        ];
        $this->assertEquals($expectedResult, $this->actionColumn->prepareDataSource($testData));
    }
}
