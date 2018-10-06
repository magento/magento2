<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Entity\Product\Attribute\Design\Options;

/**
 * Entity/Attribute/Model - select product design options container from config
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Container extends \Magento\Eav\Model\Entity\Attribute\Source\Config
{
    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|false
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        if (count($options) > 0) {
            foreach ($options as $option) {
                if (isset($option['value']) && $option['value'] == $value) {
                    return __($option['label']);
                }
            }
        }
        if (isset($options[$value])) {
            return $option[$value];
        }
        return false;
    }
}
