<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\TestCase;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Config\Test\TestStep\SetupConfigurationStep;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Constraint\ConstraintFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Swatches\Test\Constraint\AssertSwatchesVisibilityInCategory;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Attribute type swatch is created.
 * 2. Configurable product with swatch is created.
 * 3. Configurable product assigned to category.
 *
 * Steps:
 * 1. Go to Backend.
 * 2. Navigate to Stores > Configuration > Catalog > Catalog > Storefront.
 * 3. Set Show Swatches in Product List = No.
 * 4. Save configuration.
 * 5. Clean cache.
 * 6. Go to storefront > category with configurable product.
 * 7. Check swatches not visible in catalog item.
 * 8. Navigate to Stores > Configuration > Catalog > Catalog > Storefront.
 * 9. Set Show Swatches in Product List = Yes.
 * 10. Save configuration.
 * 11. Clean cache.
 * 12. Go to storefront > category with configurable product.
 * 13. Check swatches are visible in catalog item.
 *
 * @group Swatches (MX)
 * @ZephyrId MAGETWO-65485
 */
class CheckSwatchesInCategoryPageTest extends Injectable
{
    /**
     * Category page.
     *
     * @var CatalogCategoryView
     */
    protected $catalogCategoryView;

    /**
     * Index page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Factory for creation SetupConfigurationStep.
     *
     * @var TestStepFactory
     */
    protected $testStepFactory;

    /**
     * Factory for creation AssertSwatchesVisibilityInCategory.
     *
     * @var ConstraintFactory
     */
    protected $constraintFactory;

    /**
     * @param CatalogCategoryView $catalogCategoryView
     * @param CmsIndex $cmsIndex
     * @param TestStepFactory $testStepFactory
     * @param ConstraintFactory $constraintFactory
     */
    public function __prepare(
        CatalogCategoryView $catalogCategoryView,
        CmsIndex $cmsIndex,
        TestStepFactory $testStepFactory,
        ConstraintFactory $constraintFactory
    ) {
        $this->catalogCategoryView = $catalogCategoryView;
        $this->cmsIndex = $cmsIndex;
        $this->testStepFactory = $testStepFactory;
        $this->constraintFactory = $constraintFactory;
    }

    /**
     * Test check swatches on category page run.
     *
     * @param ConfigurableProduct $product
     * @return array
     */
    public function test(ConfigurableProduct $product)
    {
        //Preconditions:
        $product->persist();

        //Steps:
        $this->testStepFactory->create(
            SetupConfigurationStep::class,
            ['configData' => 'disable_swatches_visibility_in_catalog', 'flushCache' => true]
        )->run();

        /** @var AssertSwatchesVisibilityInCategory $assertSwatchesVisibility */
        $assertSwatchesVisibility = $this->constraintFactory->get(
            AssertSwatchesVisibilityInCategory::class
        );
        $assertSwatchesVisibility->processAssert(
            $this->catalogCategoryView,
            $this->cmsIndex,
            $product,
            false
        );

        $this->testStepFactory->create(
            SetupConfigurationStep::class,
            ['configData' => 'enable_swatches_visibility_in_catalog', 'flushCache' => true]
        )->run();

        return ['product' => $product];
    }
}
