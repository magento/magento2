<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Source\Design;

class Robots implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
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
