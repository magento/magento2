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
use Magento\Framework\App\ObjectManager;
use Magento\Mtf\Constraint\ConstraintFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\Util\Command\Cli\Cache;
use Magento\Mtf\Util\Command\Cli\Indexer;
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
 * 1. Go to Backend
 * 2. Navigate to Stores > Configuration > Catalog > Catalog > Storefront.
 * 3. Set Show Swatches in Product List = No.
 * 4. Save configuration.
 * 5. Clean cache.
 * 6. Go to storefron category.
 * 7. Check swatches aren't visible in catalog item.
 * 8. Set Show Swatches in Product List = Yes.
 * 9  Save configuration.
 * 10. Clean cache.
 * 11 Perform assertions.
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
     * Cache.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * @param CatalogCategoryView $catalogCategoryView
     * @param CmsIndex $cmsIndex
     * @param TestStepFactory $testStepFactory
     * @param ConstraintFactory $constraintFactory
     * @param Cache $cache
     */
    public function __prepare(
        CatalogCategoryView $catalogCategoryView,
        CmsIndex $cmsIndex,
        TestStepFactory $testStepFactory,
        ConstraintFactory $constraintFactory,
        Cache $cache
    ) {
        $this->catalogCategoryView = $catalogCategoryView;
        $this->cmsIndex = $cmsIndex;
        $this->testStepFactory = $testStepFactory;
        $this->constraintFactory = $constraintFactory;
        $this->cache = $cache;
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
        $configStep = $this->testStepFactory->create(
            SetupConfigurationStep::class,
            ['configData' => 'swatches_visibility_in_catalog', 'flushCache' => true]
        );
        $configStep->run();

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

        $configStep->cleanUp();

        return ['product' => $product];
    }
}
