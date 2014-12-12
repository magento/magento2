<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Bundle\Model\Source\Option\Selection\Price;

/**
 * Extended Attributes Source Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => '0', 'label' => __('Fixed')], ['value' => '1', 'label' => __('Percent')]];
    }
}
