<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section;

use Magento\Ui\Test\Block\Adminhtml\Section;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Product details section.
 */
class ProductDetails extends Section
{
    /**
     * Locator for category ids.
     *
     * @var string
     */
    protected $categoryIds = '.admin__field[data-index="category_ids"]';

    /**
     * Locator for following sibling of category element.
     *
     * @var string
     */
    protected $newCategoryRootElement = '.product_form_product_form_create_category_modal';

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

    /**
     * Fill data to fields on section.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function setFieldsData(array $fields, SimpleElement $element = null)
    {
        $data = $this->dataMapping($fields);
        // Select attribute set
        if (isset($data['attribute_set_id'])) {
            $this->_fill([$data['attribute_set_id']], $element);
            unset($data['attribute_set_id']);
        }
        // Select categories
        if (isset($data['category_ids'])) {
            if (isset($fields['category_ids']['source'])
                && $fields['category_ids']['source']->getCategories() !== null
                && !$fields['category_ids']['source']->getCategories()[0]->hasData('id')
            ) {
                $this->blockFactory->create(
                    'Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\ProductDetails\NewCategoryIds',
                    ['element' => $this->browser->find($this->newCategoryRootElement)]
                )->addNewCategory($fields['category_ids']['source']->getCategories()[0]);
            } else {
                $this->_fill([$data['category_ids']], $element);
            }
            unset($data['category_ids']);
        }

        $this->_fill($data, $element);

        return $this;
    }
}
