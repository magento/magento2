<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Mtf\TestCase\Injectable;

/**
 * Class AbstractAddRelatedProductsEntityTest
 * Base class for add related products entity test
 */
abstract class AbstractAddRelatedProductsEntityTest extends Injectable
{
    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Catalog product index page on backend
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Catalog product view page on backend
     *
     * @var CatalogProductNew
     */
    protected $catalogProductNew;

    /**
     * Type of related products
     *
     * @var string
     */
    protected $typeRelatedProducts = '';

    /**
     * Prepare data
     *
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Inject data
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductNew $catalogProductNew
     * @return void
     */
    public function __inject(CatalogProductIndex $catalogProductIndex, CatalogProductNew $catalogProductNew)
    {
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductNew = $catalogProductNew;
    }

    /**
     * Get product by data
     *
     * @param string $productData
     * @param array $relatedProductsData
     * @return FixtureInterface
     */
    protected function getProductByData($productData, array $relatedProductsData)
    {
        list($fixtureName, $dataSet) = explode('::', $productData);
        $relatedProductsPresets = [];
        foreach ($relatedProductsData as $type => $presets) {
            $relatedProductsPresets[$type]['presets'] = $presets;
        }

        return $this->fixtureFactory->createByCode(
            $fixtureName,
            [
                'dataSet' => $dataSet,
                'data' => $relatedProductsPresets
            ]
        );
    }

    /**
     * Create and save product
     *
     * @param FixtureInterface $product
     * @return void
     */
    protected function createAndSaveProduct(FixtureInterface $product)
    {
        $dataConfig = $product->getDataConfig();
        $typeId = isset($dataConfig['type_id']) ? $dataConfig['type_id'] : null;

        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getGridPageActionBlock()->addProduct($typeId);
        $this->catalogProductNew->getProductForm()->fill($product);
        $this->catalogProductNew->getFormPageActions()->save($product);
    }
}
