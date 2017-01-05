<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Bulk\BulkSummaryInterface;

/**
 * Class NotificationDismissActions
 */
class NotificationDismissActions extends Column
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
            if (isset($item['status'])
                && ($item['status'] === BulkSummaryInterface::FINISHED_SUCCESSFULLY
                || $item['status'] === BulkSummaryInterface::FINISHED_WITH_FAILURE)
            ) {
                $item[$this->getData('name')]['dismiss'] = [
                    'callback' => [
                        [
                            'provider' => 'ns = notification_area, index = columns',
                            'target' => 'dismiss',
                            'params' => [
                                0 => $item['uuid'],
                            ],
                        ],
                    ],
                    'href' => '#',
                    'label' => __('Dismiss'),
                ];
            }
        }

        return $dataSource;
    }
}
