<?php
/**
 * Select attributes suitable for product variations generation
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Product\Configurable;

/**
 * @api
 * @SuppressWarnings(PHPMD.LongVariable)
 * @api
 */
class AttributeSelector extends \Magento\Backend\Block\Template
{
    /**
     * Attribute set creation action URL
     *
     * @return string
     */
    public function getAttributeSetCreationUrl()
    {
        return $this->getUrl('*/product_set/save');
    }

    /**
     * Get options for suggest widget
     *
     * @return array
     */
    public function getSuggestWidgetOptions()
    {
        return [
            'source' => $this->getUrl('*/product_attribute/suggestConfigurableAttributes'),
            'minLength' => 0,
            'className' => 'category-select',
            'showAll' => true
        ];
    }
}
