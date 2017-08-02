<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source\Design;

/**
 * @api
 * @since 2.0.0
 */
class Robots implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'INDEX,FOLLOW', 'label' => 'INDEX, FOLLOW'],
            ['value' => 'NOINDEX,FOLLOW', 'label' => 'NOINDEX, FOLLOW'],
            ['value' => 'INDEX,NOFOLLOW', 'label' => 'INDEX, NOFOLLOW'],
            ['value' => 'NOINDEX,NOFOLLOW', 'label' => 'NOINDEX, NOFOLLOW']
        ];
    }
}
