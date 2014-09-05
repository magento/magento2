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

namespace Magento\GroupedProduct\Test\Constraint;

use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\GroupedProduct\Test\Fixture\CatalogProductGrouped;

/**
 * Class AssertGroupedProductsDefaultQty
 */
class AssertGroupedProductsDefaultQty extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that default qty for sub products in grouped product displays according to dataset on product page.
     *
     * @param CatalogProductView $groupedProductView
     * @param CatalogProductGrouped $product
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $groupedProductView,
        CatalogProductGrouped $product,
        Browser $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $groupedBlock = $groupedProductView->getGroupedViewBlock()->getGroupedProductBlock();
        $groupedProduct = $product->getData();

        foreach ($groupedProduct['associated']['assigned_products'] as $item) {
            \PHPUnit_Framework_Assert::assertEquals(
                $groupedBlock->getQty($item['id']),
                $item['qty'],
                'Default qty for sub product "' . $item['name'] . '" in grouped product according to dataset.'
            );
        }
    }

    /**
     * Text of Visible in grouped assert for default qty for sub products
     *
     * @return string
     */
    public function toString()
    {
        return 'Default qty for sub products in grouped product displays according to dataset on product page.';
    }
}
