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
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Class AssertTierPriceOnProductPage
 */
class AssertTierPriceOnProductPage extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assertion that tier prices are displayed correctly
     *
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        FixtureInterface $product
    ) {
        // TODO fix initialization url for frontend page
        //Open product view page
        $catalogProductView->init($product);
        $catalogProductView->open();

        //Process assertions
        $this->assertTierPrice($product, $catalogProductView);
    }

    /**
     * Verify product tier price on product view page
     *
     * @param FixtureInterface $product
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    protected function assertTierPrice(FixtureInterface $product, CatalogProductView $catalogProductView)
    {
        $noError = true;
        $match = [];
        $index = 1;
        $viewBlock = $catalogProductView->getViewBlock();
        $tierPrices = $product->getTierPrice();

        foreach ($tierPrices as $tierPrice) {
            $text = $viewBlock->getTierPrices($index++);
            $noError = (bool)preg_match('#^[^\d]+(\d+)[^\d]+(\d+(?:(?:,\d+)*)+(?:.\d+)*).*#i', $text, $match);
            if (!$noError) {
                break;
            }
            if (count($match) < 2
                && $match[1] != $tierPrice['price_qty']
                || $match[2] !== number_format($tierPrice['price'], 2)
            ) {
                $noError = false;
                break;
            }
        }

        \PHPUnit_Framework_Assert::assertTrue($noError, 'Product tier price on product page is not correct.');
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Tier price is displayed on the product page.';
    }
}
