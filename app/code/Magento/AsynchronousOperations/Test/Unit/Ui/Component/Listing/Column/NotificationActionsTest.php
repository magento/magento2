<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Ui\Component\Listing\Column;

use Magento\AsynchronousOperations\Model\BulkSummary;
use Magento\AsynchronousOperations\Ui\Component\Listing\Column\NotificationActions;
use Magento\Framework\Bulk\BulkSummaryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationActionsTest extends TestCase
{
    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * @var UiComponentFactory|MockObject
     */
    private $uiComponentFactory;

    /**
     * @var NotificationActions
     */
    private $actionColumn;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->context = $this->getMockForAbstractClass(ContextInterface::class);
        $this->uiComponentFactory = $this->createMock(UiComponentFactory::class);
        $processor = $this->getMockBuilder(Processor::class)
            ->addMethods(['getProcessor'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $objectManager = new ObjectManager($this);
        $this->actionColumn = $objectManager->getObject(
            NotificationActions::class,
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
                BulkSummary::BULK_ID => 'uuid-2',
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
                    'details' => [
                        'href' => '#',
                        'label' => __('View Details'),
                        'callback' => [
                            [
                                'provider' => 'notification_area.notification_area.modalContainer.modal.insertBulk',
                                'target' => 'destroyInserted',
                            ],
                            [
                                'provider' => 'notification_area.notification_area.modalContainer.modal.insertBulk',
                                'target' => 'updateData',
                                'params' => [
                                    BulkSummary::BULK_ID => 'uuid-1',
                                ],
                            ],
                            [
                                'provider' => 'notification_area.notification_area.modalContainer.modal',
                                'target' => 'openModal',
                            ],
                            [
                                'provider' => 'ns = notification_area, index = columns',
                                'target' => 'dismiss',
                                'params' => ['uuid-1'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                BulkSummary::BULK_ID => 'uuid-2',
                'actions' => [
                    'details' => [
                        'href' => '#',
                        'label' => __('View Details'),
                        'callback' => [
                            [
                                'provider' => 'notification_area.notification_area.modalContainer.modal.insertBulk',
                                'target' => 'destroyInserted',
                            ],
                            [
                                'provider' => 'notification_area.notification_area.modalContainer.modal.insertBulk',
                                'target' => 'updateData',
                                'params' => [
                                    BulkSummary::BULK_ID => 'uuid-2',
                                ],
                            ],
                            [
                                'provider' => 'notification_area.notification_area.modalContainer.modal',
                                'target' => 'openModal',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expectedResult, $this->actionColumn->prepareDataSource($testData));
    }
}
