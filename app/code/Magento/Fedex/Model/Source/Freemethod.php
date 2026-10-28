<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Fedex\Model\Source;

/**
 * Fedex freemethod source implementation
 */
class Freemethod extends \Magento\Fedex\Model\Source\Method
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $arr = parent::toOptionArray();
        array_unshift($arr, ['value' => '', 'label' => __('None')]);
        return $arr;
    }
}
