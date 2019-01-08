<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model\Config\Source;

/**
 * Options provider for weight units list
 *
 * @api
 * @since 100.0.2
 */
class WeightUnit implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var string
     */
    const CODE_LBS = 'lbs';

    /**
     * @var string
     */
    const CODE_KGS = 'kgs';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CODE_LBS, 'label' => __('lbs')],
            ['value' => self::CODE_KGS, 'label' => __('kgs')]
        ];
    }
}
