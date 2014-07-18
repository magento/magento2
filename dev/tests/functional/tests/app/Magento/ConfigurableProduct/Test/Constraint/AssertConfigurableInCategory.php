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

use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\ConfigurableProduct\Test\Fixture\CatalogProductConfigurable;

/**
 * Class AssertProductInCategory
 */
class AssertConfigurableInCategory extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert configurable product, corresponds to the category page
     *
     * @param CatalogCategoryView $catalogCategoryView
     * @param CmsIndex $cmsIndex
     * @param CatalogProductConfigurable $configurable
     * @param CatalogCategory $category
     * @return void
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        CmsIndex $cmsIndex,
        CatalogProductConfigurable $configurable,
        CatalogCategory $category
    ) {
        //Open category view page
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($category->getName());

        //process asserts
        $this->assertPrice($configurable, $catalogCategoryView);
    }

    /**
     * Verify product price on category view page
     *
     * @param CatalogProductConfigurable $configurable
     * @param CatalogCategoryView $catalogCategoryView
     * @return void
     */
    protected function assertPrice(
        CatalogProductConfigurable $configurable,
        CatalogCategoryView $catalogCategoryView
    ) {
        /** @var \Magento\ConfigurableProduct\Test\Fixture\CatalogProductConfigurable\Price $priceFixture */
        $priceFixture = $configurable->getDataFieldConfig('price')['source'];
        $pricePresetData = $priceFixture->getPreset();

        //Regular price verification
        if (isset($pricePresetData['category_special_price'])) {
            $regularPrice = $catalogCategoryView->getListProductBlock()
                ->getProductPriceBlock($configurable->getName())
                ->getRegularPrice();
            \PHPUnit_Framework_Assert::assertEquals(
                $pricePresetData['category_price'],
                $regularPrice,
                'Product regular price on category page is not correct.'
            );
            //Special price verification
            $specialPrice = $catalogCategoryView->getListProductBlock()->getProductPriceBlock(
                $configurable->getName()
            )->getSpecialPrice();
            \PHPUnit_Framework_Assert::assertEquals(
                $pricePresetData['category_special_price'],
                $specialPrice,
                'Product special price on category page is not correct.'
            );
        } else {
            //Price verification
            $price = $catalogCategoryView->getListProductBlock()
                ->getProductPriceBlock($configurable->getName())
                ->getPrice();
            \PHPUnit_Framework_Assert::assertEquals(
                $price['price_regular_price'],
                $pricePresetData['category_price'],
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
        return 'Product price on category page is not correct.';
    }
}
