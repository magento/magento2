<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class Nooptreq implements \Magento\Framework\Option\ArrayInterface
{
    const VALUE_NO = '';
    const VALUE_OPTIONAL = 'opt';
    const VALUE_REQUIRED = 'req';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::VALUE_NO, 'label' => __('No')],
            ['value' => self::VALUE_OPTIONAL, 'label' => __('Optional')],
            ['value' => self::VALUE_REQUIRED, 'label' => __('Required')]
        ];
    }
}
