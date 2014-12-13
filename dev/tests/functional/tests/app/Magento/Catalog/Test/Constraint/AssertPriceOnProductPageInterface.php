<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Block\Product\View;
use Mtf\Fixture\FixtureInterface;

/**
 * Interface AssertPriceOnProductPageInterface
 * Interface for Constraints price on product page classes
 */
interface AssertPriceOnProductPageInterface
{
    /**
     * Verify product price on product view page
     *
     * @param FixtureInterface $product
     * @param View $productViewBlock
     * @return void
     */
    public function assertPrice(FixtureInterface $product, View $productViewBlock);

    /**
     * Set $errorMessage for constraint
     *
     * @param string $errorMessage
     * @return void
     */
    public function setErrorMessage($errorMessage);
}
