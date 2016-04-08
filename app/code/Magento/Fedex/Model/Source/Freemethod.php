<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Fedex\Model\Source;

/**
 * Fedex freemethod source implementation
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Freemethod extends \Magento\Fedex\Model\Source\Method
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $arr = parent::toOptionArray();
        array_unshift($arr, ['value' => '', 'label' => __('None')]);
        return $arr;
    }
}
