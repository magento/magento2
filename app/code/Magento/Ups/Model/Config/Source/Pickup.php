<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Model\Config\Source;

/**
 * Class Pickup
 */
class Pickup extends \Magento\Ups\Model\Config\Source\Generic
{
    /**
     * Carrier code
     *
     * @var string
     */
    protected $_code = 'pickup';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $ups = $this->carrierConfig->getCode($this->_code);
        $arr = [];
        foreach ($ups as $k => $v) {
            $arr[] = ['value' => $k, 'label' => __($v['label'])];
        }
        return $arr;
    }
}
