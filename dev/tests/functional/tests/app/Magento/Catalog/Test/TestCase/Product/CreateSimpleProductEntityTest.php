<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Login to the backend.
 * 2. Navigate to Products > Catalog.
 * 3. Start to create simple product.
 * 4. Fill in data according to data set.
 * 5. Save Product.
 * 6. Perform appropriate assertions.
 *
 * @group Products_(CS)
 * @ZephyrId MAGETWO-23414
 */
class CreateSimpleProductEntityTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Prepare data.
     *
     * @param Category $category
     * @return array
     */
    public function __prepare(Category $category)
    {
        $category->persist();

        return [
            'category' => $category
        ];
    }

    /**
     * Run create product simple entity test.
     *
     * @param CatalogProductSimple $product
     * @param Category $category
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     * @param string $configData
     * @return array
     */
    public function testCreate(
        CatalogProductSimple $product,
        Category $category,
        CatalogProductIndex $productGrid,
        CatalogProductNew $newProductPage,
        $configData = null
    ) {
        $this->configData = $configData;

        // Preconditions
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData]
        )->run();

        // Steps
        $productGrid->open();
        $productGrid->getGridPageActionBlock()->addProduct('simple');
        $newProductPage->getProductForm()->fill($product, null, $category);
        $newProductPage->getFormPageActions()->save();

        return ['product' => $product];
    }

    /**
     * Clean data after running test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
