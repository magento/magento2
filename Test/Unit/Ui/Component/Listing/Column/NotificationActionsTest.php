<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Ui\Component\Listing\Column;

use Magento\AsynchronousOperations\Model\BulkSummary;
use Magento\Framework\Bulk\BulkSummaryInterface;

class NotificationActionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentFactory;

    /**
     * @var \Magento\AsynchronousOperations\Ui\Component\Listing\Column\NotificationActions
     */
    private $actionColumn;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->context = $this->getMock(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class,
            [],
            [],
            '',
            false
        );
        $this->uiComponentFactory = $this->getMock(
            \Magento\Framework\View\Element\UiComponentFactory::class,
            [],
            [],
            '',
            false
        );
        $processor = $this->getMock(
            \Magento\Framework\View\Element\UiComponent\Processor::class,
            ['getProcessor'],
            [],
            '',
            false
        );
        $this->context->expects($this->any())->method('getProcessor')->will($this->returnValue($processor));
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->actionColumn = $objectManager->getObject(
            \Magento\AsynchronousOperations\Ui\Component\Listing\Column\NotificationActions::class,
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
