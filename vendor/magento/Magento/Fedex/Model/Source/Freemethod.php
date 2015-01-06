<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
