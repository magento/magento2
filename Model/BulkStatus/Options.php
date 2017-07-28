<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model\BulkStatus;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;

/**
 * Class Options
 * @since 2.2.0
 */
class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     * @since 2.2.0
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => BulkSummaryInterface::NOT_STARTED,
                'label' => 'Not Started'
            ],
            [
                'value' => BulkSummaryInterface::IN_PROGRESS,
                'label' => 'In Progress'
            ],
            [
                'value' => BulkSummaryInterface::FINISHED_SUCCESSFULLY,
                'label' => 'Finished Successfully'
            ],
            [
                'value' => BulkSummaryInterface::FINISHED_WITH_FAILURE,
                'label' => 'Finished with Failure'
            ]
        ];
    }
}
