<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Ui\Component\Listing\Columns\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Image source filter options
 */
class Options implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'Local',
                'label' =>  __('Uploaded Locally'),
            ],
        ];
    }
}
