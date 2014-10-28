<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Magento\Catalog\Test\Block\Product\Grouped\AssociatedProducts;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;

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
        $element = $context ? : $this->_rootElement;
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
