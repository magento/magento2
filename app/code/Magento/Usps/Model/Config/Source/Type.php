<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Usps\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Type implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'USPS_XML', 'label' => __('USPS Web Tools API')],
            ['value' => 'USPS_REST', 'label' => __('USPS Rest APIs')]
        ];
    }
}
