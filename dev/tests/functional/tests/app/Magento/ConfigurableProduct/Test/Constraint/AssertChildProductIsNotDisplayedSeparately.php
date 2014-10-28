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

use Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\CatalogSearch\Test\Page\CatalogsearchResult;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductInjectable;

/**
 * Class AssertChildProductIsNotDisplayedSeparately
 * Assert that products generated during configurable product creation - are not visible on frontend(by default).
 */
class AssertChildProductIsNotDisplayedSeparately extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'middle';

    /**
     * Assert that products generated during configurable product creation - are not visible on frontend(by default).
     *
     * @param CatalogSearchResult $catalogSearchResult
     * @param CmsIndex $cmsIndex
     * @param ConfigurableProductInjectable $product
     * @return void
     */
    public function processAssert(
        CatalogsearchResult $catalogSearchResult,
        CmsIndex $cmsIndex,
        ConfigurableProductInjectable $product
    ) {
        $configurableAttributesData = $product->getConfigurableAttributesData();
        $errors = [];

        $cmsIndex->open();
        foreach ($configurableAttributesData['matrix'] as $variation) {
            $cmsIndex->getSearchBlock()->search($variation['sku']);

            $isVisibleProduct = $catalogSearchResult->getListProductBlock()->isProductVisible($variation['name']);
            while (!$isVisibleProduct && $catalogSearchResult->getBottomToolbar()->nextPage()) {
                $isVisibleProduct = $catalogSearchResult->getListProductBlock()->isProductVisible($product->getName());
            }
            if ($isVisibleProduct) {
                $errors[] = sprintf(
                    "\nChild product with sku: \"%s\" is visible on frontend(by default).",
                    $variation['sku']
                );
            }
        }

        \PHPUnit_Framework_Assert::assertEmpty($errors, implode(' ', $errors));
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Child products generated during configurable product creation are not visible on frontend(by default)';
    }
}
