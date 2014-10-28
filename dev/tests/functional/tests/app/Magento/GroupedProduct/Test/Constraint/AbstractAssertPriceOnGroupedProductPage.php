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
use Mtf\Fixture\InjectableFixture;
use Mtf\Constraint\AbstractConstraint;
use Magento\GroupedProduct\Test\Fixture\GroupedProductInjectable;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Catalog\Test\Constraint\AssertPriceOnProductPageInterface;

/**
 * Class AbstractAssertPriceOnGroupedProductPage
 * Assert that displayed price on grouped product page equals passed from fixture
 */
abstract class AbstractAssertPriceOnGroupedProductPage extends AbstractConstraint
{
    /**
     * Format error message
     *
     * @var string
     */
    protected $errorMessage;

    /**
     * Successful message
     *
     * @var string
     */
    protected $successfulMessage;

    /**
     * Verify product price on grouped product view page
     *
     * @param GroupedProductInjectable $product
     * @param CatalogProductView $catalogProductView
     * @param AssertPriceOnProductPageInterface $object
     * @param Browser $browser
     * @param string $typePrice [optional]
     * @return bool|string
     */
    protected function processAssertPrice(
        GroupedProductInjectable $product,
        CatalogProductView $catalogProductView,
        AssertPriceOnProductPageInterface $object,
        Browser $browser,
        $typePrice = ''
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        $groupedData = $product->getAssociated();
        /** @var InjectableFixture $subProduct */
        foreach ($groupedData['products'] as $key => $subProduct) {
            //Process assertions
            $catalogProductView->getViewBlock()
                ->{'item' . $typePrice . 'PriceProductBlock'}(++$key);
            $object->setErrorMessage(sprintf($this->errorMessage, $subProduct->getData('name')));
            $object->assertPrice($subProduct, $catalogProductView, 'Grouped');
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return $this->successfulMessage;
    }
}
