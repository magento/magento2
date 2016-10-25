<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

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
 * @group Products
 * @ZephyrId MAGETWO-59861
 */
class CreateSimpleProductEntityByAttributeMaskSkuTest extends Injectable
{
    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Should cache be flushed
     *
     * @var bool
     */
    private $flushCache;

    /**
     * @var \Magento\Mtf\Fixture\FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Run create product simple entity by attribute mask SKU test.
     *
     * @param CatalogProductSimple $product
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     * @param string $configData
     * @param bool $flushCache
     * @return array
     */
    public function testCreate(
        CatalogProductSimple $product,
        CatalogProductIndex $productGrid,
        CatalogProductNew $newProductPage,
        \Magento\Mtf\Fixture\FixtureFactory $fixtureFactory,
        $flushCache = false,
        $configData = null
    ) {
        $this->configData = $configData;
        $this->flushCache = $flushCache;
        $this->fixtureFactory = $fixtureFactory;

        // Preconditions
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'flushCache' => $this->flushCache]
        )->run();

        // Steps
        $productGrid->open();
        $productGrid->getGridPageActionBlock()->addProduct('simple');
        $newProductPage->getProductForm()->fill($product);
        $newProductPage->getFormPageActions()->save();

        $skuMask = $this->prepareSkuByMask($product);

        $productSimple = $fixtureFactory->createByCode(
            'catalogProductSimple',
            ['data' => array_merge($product->getData(), ['sku' => $skuMask])]
        );

        return ['product' => $productSimple];
    }

    /**
     * Obtains product sku based on attributes define in Stores > Configuration->Catalog > Catalog > Mask for SKU
     *
     * @param CatalogProductSimple $product
     * @return string
     */
    private function prepareSkuByMask(CatalogProductSimple $product)
    {
        $productData = $product->getData();
        $skuMask = '';
        $config = $this->fixtureFactory->createByCode('configData', ['dataset' => $this->configData]);
        $section = $config->getData('section');
        if (is_array($section) && array_key_exists('catalog/fields_masks/sku', $section)) {
            $skuMask = $section['catalog/fields_masks/sku']['value'];
        }

        $attributesInPattern = [];
        $count = preg_match_all('/{{(\w+)}}/', $skuMask, $matches);
        if ($count > 0 && is_array($matches[0])) {
            foreach ($matches[1] as $attributeName) {
                if (array_key_exists($attributeName, $productData)) {
                    $attributesInPattern[$attributeName] = $productData[$attributeName];
                }
            }
        }
        foreach ($attributesInPattern as $attributeName => $attributeValue) {
            $skuMask = str_replace('{{' . $attributeName . '}}', $attributeValue, $skuMask);
        }
        return $skuMask;
    }

    /**
     * Clean data after running test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true, 'flushCache' => $this->flushCache]
        )->run();
    }
}
