<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
