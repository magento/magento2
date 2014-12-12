<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Product\Grouped\AssociatedProducts;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Factory\Factory;

/**
 * Class ListAssociatedProducts
 * List associated products on the page
 */
class ListAssociatedProducts extends Block
{
    /**
     * Getting block products
     *
     * @param string $productId
     * @param Element $context
     * @return ListAssociatedProducts\Product
     */
    private function getProductBlock($productId, Element $context = null)
    {
        $element = $context ?: $this->_rootElement;
        return Factory::getBlockFactory()
            ->getMagentoCatalogProductGroupedAssociatedProductsListAssociatedProductsProduct(
                $element->find(
                    sprintf("//tr[td/input[@data-role='id' and @value='%s']]", $productId),
                    Locator::SELECTOR_XPATH
                )
            );
    }

    /**
     * Filling options products
     *
     * @param array $data
     * @param Element $element
     */
    public function fillProductOptions(array $data, Element $element = null)
    {
        $productBlock = $this->getProductBlock($data['product_id']['value'], $element);
        $productBlock->fillQty($data['selection_qty']['value']);
    }
}
