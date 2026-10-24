<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\AsynchronousOperations\Model\BulkStatus;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;

class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => BulkSummaryInterface::NOT_STARTED,
                'label' => __('Not Started')
            ],
            [
                'value' => BulkSummaryInterface::IN_PROGRESS,
                'label' => __('In Progress')
            ],
            [
                'value' => BulkSummaryInterface::FINISHED_SUCCESSFULLY,
                'label' => __('Finished Successfully')
            ],
            [
                'value' => BulkSummaryInterface::FINISHED_WITH_FAILURE,
                'label' => __('Finished with Failure')
            ]
        ];
    }
}
