<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FilterConditionType implements OptionSourceInterface
{
    public const PARTIAL_MATCH = 0;
    public const PREFIX_MATCH = 1;
    public const FULL_MATCH = 2;

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PARTIAL_MATCH, 'label' => __('Partial Match')],
            ['value' => self::PREFIX_MATCH, 'label' => __('Prefix Match')],
            ['value' => self::FULL_MATCH, 'label' => __('Full Match')],
        ];
    }
}
