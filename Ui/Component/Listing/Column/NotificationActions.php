<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Bulk\BulkSummaryInterface;

/**
 * Class Actions
 */
class NotificationActions extends Column
{
    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (isset($item['uuid'])) {
                $item[$this->getData('name')]['details'] = [
                    'callback' => [
                        [
                            'provider' => 'notification_area.notification_area.modalContainer.modal.insertBulk',
                            'target' => 'destroyInserted',
                        ],
                        [
                            'provider' => 'notification_area.notification_area.modalContainer.modal.insertBulk',
                            'target' => 'updateData',
                            'params' => [
                                'uuid' => $item['uuid'],
                            ],
                        ],
                        [
                            'provider' => 'notification_area.notification_area.modalContainer.modal',
                            'target' => 'openModal',
                        ],
                    ],
                    'href' => '#',
                    'label' => __('View Details'),
                ];

                if (isset($item['status'])
                    && ($item['status'] === BulkSummaryInterface::FINISHED_SUCCESSFULLY
                    || $item['status'] === BulkSummaryInterface::FINISHED_WITH_FAILURE)
                ) {
                    $item[$this->getData('name')]['details']['callback'][] = [
                        'provider' => 'ns = notification_area, index = columns',
                        'target' => 'dismiss',
                        'params' => [
                            0 => $item['uuid'],
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
