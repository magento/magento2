<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Mtf\Client\Driver\Selenium\Element\SuggestElement;

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
