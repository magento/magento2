<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Model\Config\Source;

/**
 * Class Freemethod
 * @since 2.0.0
 */
class Freemethod extends \Magento\Ups\Model\Config\Source\Method
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $arr = parent::toOptionArray();
        array_unshift($arr, ['value' => '', 'label' => __('None')]);
        return $arr;
    }
}
