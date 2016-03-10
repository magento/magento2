<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit\Section;

use Magento\Ui\Test\Block\Adminhtml\Section;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Element;

/**
 * Section "Attributes".
 */
class Attributes extends Section
{
    /**
     * Fixture mapping.
     *
     * @param array|null $fields
     * @param string|null $parent
     * @return array
     */
    protected function dataMapping(array $fields = null, $parent = null)
    {
        if (isset($fields['custom_attribute'])) {
            $this->placeholders = ['attribute_code' => $fields['custom_attribute']['value']['code']];
            $this->applyPlaceholders();
        }
        return parent::dataMapping($fields, $parent);
    }
}
