<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Model\Config\Source;

/**
 * Class Pickup
 * @since 2.0.0
 */
class Pickup extends \Magento\Ups\Model\Config\Source\Generic
{
    /**
     * Carrier code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_code = 'pickup';

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
