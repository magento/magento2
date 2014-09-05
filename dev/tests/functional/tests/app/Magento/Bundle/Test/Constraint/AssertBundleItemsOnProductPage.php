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

namespace Magento\Bundle\Test\Constraint;

use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Magento\Bundle\Test\Fixture\CatalogProductBundle;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Class AssertBundleItemsOnProductPage
 */
class AssertBundleItemsOnProductPage extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that displayed product bundle items data on product page equals passed from fixture preset
     *
     * @param CatalogProductView $catalogProductView
     * @param CatalogProductBundle $product
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        CatalogProductBundle $product,
        Browser $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $catalogProductView->getViewBlock()->clickCustomize();
        $result = $this->displayedBundleBlock($catalogProductView, $product);
        \PHPUnit_Framework_Assert::assertTrue(empty($result), $result);
    }

    /**
     * Displayed bundle block on frontend with correct fixture product
     *
     * @param CatalogProductView $catalogProductView
     * @param CatalogProductBundle $product
     * @return string|null
     */
    protected function displayedBundleBlock(CatalogProductView $catalogProductView, CatalogProductBundle $product)
    {
        $fields = $product->getData();
        $bundleOptions = $fields['bundle_selections']['bundle_options'];
        if (!isset($bundleOptions)) {
            return 'Bundle options data on product page is not equals to fixture preset.';
        }

        $catalogProductView->getViewBlock()->clickCustomize();
        foreach ($bundleOptions as $index => $item) {
            foreach ($item['assigned_products'] as &$selection) {
                $selection = $selection['search_data'];
            }
            $result = $catalogProductView->getBundleViewBlock()->getBundleBlock()->displayedBundleItemOption(
                $item,
                ++$index
            );

            if ($result !== true) {
                return $result;
            }
        }
        return null;
    }

    /**
     * Return Text if displayed on frontend equals with fixture
     *
     * @return string
     */
    public function toString()
    {
        return 'Bundle options data on product page equals to passed from fixture preset.';
    }
}
