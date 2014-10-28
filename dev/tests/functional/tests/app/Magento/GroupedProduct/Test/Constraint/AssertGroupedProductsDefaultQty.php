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
use Mtf\Constraint\AbstractAssertForm;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\GroupedProduct\Test\Fixture\GroupedProductInjectable;

/**
 * Class AssertGroupedProductsDefaultQty
 * Assert that default qty for sub products in grouped product displays according to dataset on product page
 */
class AssertGroupedProductsDefaultQty extends AbstractAssertForm
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that default qty for sub products in grouped product displays according to dataset on product page
     *
     * @param CatalogProductView $groupedProductView
     * @param GroupedProductInjectable $product
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $groupedProductView,
        GroupedProductInjectable $product,
        Browser $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $associatedProducts = $product->getAssociated();
        $fixtureQtyData = [];
        $pageOptions = $groupedProductView->getViewBlock()->getOptions($product);
        $pageQtyData = [];

        foreach ($associatedProducts['assigned_products'] as $productData) {
            $fixtureQtyData[] = [
                'name' => $productData['name'],
                'qty' => $productData['qty']
            ];
        }
        foreach ($pageOptions['grouped_options'] as $productData) {
            $pageQtyData[] = [
                'name' => $productData['name'],
                'qty' => $productData['qty']
            ];
        }
        $fixtureQtyData = $this->sortDataByPath($fixtureQtyData, '::name');
        $pageQtyData = $this->sortDataByPath($pageQtyData, '::name');

        $error = $this->verifyData($fixtureQtyData, $pageQtyData);
        \PHPUnit_Framework_Assert::assertEmpty($error, $error);
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
