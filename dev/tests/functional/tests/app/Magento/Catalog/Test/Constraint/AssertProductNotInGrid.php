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

namespace Magento\Catalog\Test\Constraint;

use Mtf\Fixture\FixtureInterface;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Class AssertProductNotInGrid
 * Assert that Product absence on grid
 */
class AssertProductNotInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that product cannot be found by name and sku
     *
     * @param FixtureInterface|FixtureInterface[] $product
     * @param CatalogProductIndex $productGrid
     * @return void
     */
    public function processAssert($product, CatalogProductIndex $productGrid)
    {
        $products = is_array($product) ? $product : [$product];
        foreach ($products as $product) {
            $filter = ['sku' => $product->getSku(), 'name' => $product->getName()];
            $productGrid->open();
            \PHPUnit_Framework_Assert::assertFalse(
                $productGrid->getProductGrid()->isRowVisible($filter),
                "Product with sku \"{$filter['sku']}\" and name \"{$filter['name']}\" is attend in Products grid."
            );
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Assertion that product is absent in products grid.';
    }
}
