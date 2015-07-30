<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute;

use Magento\Mtf\Client\Element\SuggestElement;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

/**
 * Form Attribute Search on Product page.
 */
class AttributeSelector extends SuggestElement
{
    /**
     * Wait for search result is visible.
     *
     * @return void
     */
    public function waitResult()
    {
        try {
            $this->waitUntil(
                function () {
                    return $this->find($this->searchResult)->isVisible() ? true : null;
                }
            );
        } catch (\Exception $e) {
            // In parallel run on windows change the focus is lost on element
            // that causes disappearing of result suggest list.
        }
    }

    /**
     * Checking exist configurable attribute in search result.
     *
     * @param CatalogProductAttribute $productAttribute
     * @return bool
     */
    public function isExistAttributeInSearchResult(CatalogProductAttribute $productAttribute)
    {
        return $this->isExistValueInSearchResult($productAttribute->getFrontendLabel());
    }
}
