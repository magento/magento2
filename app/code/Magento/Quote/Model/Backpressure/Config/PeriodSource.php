<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Quote\Model\Backpressure\Config;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Provides selection of limited periods
 */
class PeriodSource implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            '60' => ['value' => '60', 'label' => __('Minute')],
            '3600' => ['value' => '3600', 'label' => __('Hour')],
            '86400' => ['value' => '86400', 'label' => __('Day')]
        ];
    }
}
