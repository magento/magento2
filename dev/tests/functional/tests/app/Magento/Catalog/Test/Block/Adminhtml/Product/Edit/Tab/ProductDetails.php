<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\ProductTab;

/**
 * Product details tab.
 */
class ProductDetails extends ProductTab
{
    /**
     * Locator for preceding sibling of category element.
     *
     * @var string
     */
    protected $categoryPrecedingSibling = '//*[@id="attribute-category_ids-container"]/preceding-sibling::div[%d]';

    /**
     * Locator for following sibling of category element.
     *
     * @var string
     */
    protected $categoryFollowingSibling = '//*[@id="attribute-category_ids-container"]/following-sibling::div[%d]';

    /**
     * Locator for following sibling of category element.
     *
     * @var string
     */
    protected $newCategoryRootElement = '.mage-new-category-dialog';

    /**
     * Fill data to fields on tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        $data = $this->dataMapping($fields);
        // Select attribute set
        if (isset($data['attribute_set_id'])) {
            $this->_fill([$data['attribute_set_id']], $element);
            unset($data['attribute_set_id']);
        }
        // Select categories
        if (isset($data['category_ids'])) {
            /* Fix browser behavior for click by hidden list result of suggest(category) element */
            $this->scrollToCategory();
            if (isset($fields['category_ids']['source'])
                && $fields['category_ids']['source']->getCategories() !== null
                && !$fields['category_ids']['source']->getCategories()[0]->hasData('id')
            ) {
                $this->blockFactory->create(
                    'Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails\NewCategoryIds',
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

    /**
     * Scroll page to "Categories" field.
     *
     * @return void
     */
    protected function scrollToCategory()
    {
        $this->_rootElement->find(sprintf($this->categoryFollowingSibling, 1), Locator::SELECTOR_XPATH)->click();
        $this->_rootElement->find(sprintf($this->categoryPrecedingSibling, 2), Locator::SELECTOR_XPATH)->click();
    }
}
