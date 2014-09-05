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

namespace Magento\ConfigurableProduct\Test\Constraint;

use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\ConfigurableProduct\Test\Fixture\CatalogProductConfigurable;

/**
 * Class AssertConfigurableView
 */
class AssertConfigurableView extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert configurable product, corresponds to the product page
     *
     * @param CatalogProductView $catalogProductView
     * @param Browser $browser
     * @param CatalogProductConfigurable $configurable
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        Browser $browser,
        CatalogProductConfigurable $configurable
    ) {
        //Open product view page
        $browser->open($_ENV['app_frontend_url'] . $configurable->getUrlKey() . '.html');

        //Process assertions
        $this->assertOnProductView($configurable, $catalogProductView);
    }

    /**
     * Assert prices on the product view Page
     *
     * @param CatalogProductConfigurable $configurable
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    protected function assertOnProductView(
        CatalogProductConfigurable $configurable,
        CatalogProductView $catalogProductView
    ) {
        /** @var \Magento\ConfigurableProduct\Test\Fixture\CatalogProductConfigurable\Price $priceFixture */
        $priceFixture = $configurable->getDataFieldConfig('price')['source'];
        $pricePresetData = $priceFixture->getPreset();

        if (isset($pricePresetData['product_special_price'])) {
            $regularPrice = $catalogProductView->getViewBlock()->getProductPriceBlock()->getRegularPrice();
            \PHPUnit_Framework_Assert::assertEquals(
                $pricePresetData['product_price'],
                $regularPrice,
                'Product regular price on product view page is not correct.'
            );

            $specialPrice = $catalogProductView->getViewBlock()->getProductPriceBlock()->getSpecialPrice();
            \PHPUnit_Framework_Assert::assertEquals(
                $pricePresetData['product_special_price'],
                $specialPrice,
                'Product special price on product view page is not correct.'
            );
        } else {
            //Price verification
            $price = $catalogProductView->getViewBlock()
                ->getProductPriceBlock($configurable->getName())
                ->getPrice();
            \PHPUnit_Framework_Assert::assertEquals(
                $price['price_regular_price'],
                $pricePresetData['product_price'],
                'Product price on category page is not correct.'
            );
        }
    }

    /**
     * Text of Visible in category assert
     *
     * @return string
     */
    public function toString()
    {
        return 'Product price on product view page is not correct.';
    }
}
