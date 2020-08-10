<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Ui\Component\Listing\Filters\Options;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Status filter options
 */
class Status implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '1', 'label' => __('Enabled')],
            ['value' => '0', 'label' => __('Disabled')]
        ];
    }
}
