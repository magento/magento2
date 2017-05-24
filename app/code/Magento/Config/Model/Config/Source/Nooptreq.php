<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source;

class Nooptreq implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
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
