<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source;

/**
 * @api
 * @since 2.0.0
 */
class Nooptreq implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('No')],
            ['value' => 'opt', 'label' => __('Optional')],
            ['value' => 'req', 'label' => __('Required')]
        ];
    }
}
