<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute;

use Mtf\Client\Element\SuggestElement;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

/**
 * Class AttributeSelector
 * Form Attribute Search on Product page
 */
class AttributeSelector extends SuggestElement
{
    /**
     * Checking exist configurable attribute in search result
     *
     * @param CatalogProductAttribute $productAttribute
     * @return bool
     */
    public function isExistAttributeInSearchResult(CatalogProductAttribute $productAttribute)
    {
        return $this->isExistValueInSearchResult($productAttribute->getFrontendLabel());
    }
}
