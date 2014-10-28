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
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Page\CatalogsearchResult;

/**
 * Class AssertProductSearchableBySku
 */
class AssertProductSearchableBySku extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Displays an error message
     *
     * @var string
     */
    protected $errorMessage = 'The product has not been found by SKU';

    /**
     * Message for passing test
     *
     * @var string
     */
    protected $successfulMessage = 'Product successfully found by SKU.';

    /**
     * Assert that product can be searched via Quick Search using searchable product attributes (Search by SKU)
     *
     * @param CatalogsearchResult $catalogSearchResult
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface $product
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function processAssert(
        CatalogsearchResult $catalogSearchResult,
        CmsIndex $cmsIndex,
        FixtureInterface $product
    ) {
        $cmsIndex->open();
        $sku = ($product->hasData('sku') !== false) ? $product->getSku() : $product->getName();
        $cmsIndex->getSearchBlock()->search($sku);

        $quantityAndStockStatus = $product->getQuantityAndStockStatus();
        $stockStatus = isset($quantityAndStockStatus['is_in_stock'])
            ? $quantityAndStockStatus['is_in_stock']
            : null;

        $isVisible = $catalogSearchResult->getListProductBlock()->isProductVisible($product->getName());
        while (!$isVisible && $catalogSearchResult->getBottomToolbar()->nextPage()) {
            $isVisible = $catalogSearchResult->getListProductBlock()->isProductVisible($product->getName());
        }

        if ($product->getVisibility() === 'Catalog' || $stockStatus === 'Out of Stock') {
            $isVisible = !$isVisible;
            list($this->errorMessage, $this->successfulMessage) = [$this->successfulMessage, $this->errorMessage];
        }

        \PHPUnit_Framework_Assert::assertTrue(
            $isVisible,
            $this->errorMessage
        );
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
