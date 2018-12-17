<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * AdminNotification update frequency source
 *
 * @package Magento\AdminNotification\Model\Config\Source
 * @codeCoverageIgnore
 * @api
 * @since 100.0.2
 */
class Frequency implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            1 => __('1 Hour'),
            2 => __('2 Hours'),
            6 => __('6 Hours'),
            12 => __('12 Hours'),
            24 => __('24 Hours')
        ];
    }
}
