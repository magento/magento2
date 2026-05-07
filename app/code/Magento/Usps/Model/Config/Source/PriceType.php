<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Usps\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PriceType implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'COMMERCIAL', 'label' => __('Commercial')],
            ['value' => 'RETAIL', 'label' => __('Retail')]
        ];
    }
}
