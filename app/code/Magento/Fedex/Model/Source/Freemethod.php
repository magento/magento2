<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Fedex\Model\Source;

/**
 * Fedex freemethod source implementation
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Freemethod extends \Magento\Fedex\Model\Source\Method
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
