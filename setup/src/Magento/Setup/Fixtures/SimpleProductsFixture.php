<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Setup\Model\Generator;

/**
 * Class SimpleProductsFixture
 */
class SimpleProductsFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 30;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $simpleProductsCount = $this->fixtureModel->getValue('simple_products', 0);
        if (!$simpleProductsCount) {
            return;
        }
        $this->fixtureModel->resetObjectManager();

        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->fixtureModel->getObjectManager()->create('Magento\Store\Model\StoreManager');
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->fixtureModel->getObjectManager()->get('Magento\Catalog\Model\Category');

        $result = [];
        //Get all websites
        $websites = $storeManager->getWebsites();
        foreach ($websites as $website) {
            $websiteCode = $website->getCode();
            //Get all groups
            $websiteGroups = $website->getGroups();
            foreach ($websiteGroups as $websiteGroup) {
                $websiteGroupRootCategory = $websiteGroup->getRootCategoryId();
                $category->load($websiteGroupRootCategory);
                $categoryResource = $category->getResource();
                //Get all categories
                $resultsCategories = $categoryResource->getAllChildren($category);
                foreach ($resultsCategories as $resultsCategory) {
                    $category->load($resultsCategory);
                    $structure = explode('/', $category->getPath());
                    $pathSize  = count($structure);
                    if ($pathSize > 1) {
                        $path = [];
                        for ($i = 0; $i < $pathSize; $i++) {
                            $path[] = $category->load($structure[$i])->getName();
                        }
                        array_shift($path);
                        $resultsCategoryName = implode('/', $path);
                    } else {
                        $resultsCategoryName = $category->getName();
                    }
                    //Deleted root categories
                    if (trim($resultsCategoryName) != '') {
                        $result[$resultsCategory] = [$websiteCode, $resultsCategoryName];
                    }
                }
            }
        }
        $result = array_values($result);

        $productWebsite = function ($index) use ($result) {
            return $result[$index % count($result)][0];
        };
        $productCategory = function ($index) use ($result) {
            return $result[$index % count($result)][1];
        };

        $generator = new Generator(
            $this->getPattern($productWebsite, $productCategory),
            $simpleProductsCount
        );
        /** @var \Magento\ImportExport\Model\Import $import */
        $import = $this->fixtureModel->getObjectManager()->create(
            'Magento\ImportExport\Model\Import',
            [
                'data' => [
                    'entity' => 'catalog_product',
                    'behavior' => 'append',
                    'validation_strategy' => 'validation-stop-on-errors'
                ]
            ]
        );
        // it is not obvious, but the validateSource() will actually save import queue data to DB
        $import->validateSource($generator);
        // this converts import queue into actual entities
        $import->importSource();
    }

    /**
     * Get pattern for product import
     *
     * @param Closure|int|string $productWebsiteClosure
     * @param Closure|int|string $productCategoryClosure
     * @return array
     */
    protected function getPattern($productWebsiteClosure, $productCategoryClosure)
    {
        return [
            'attribute_set_code'    => 'Default',
            'product_type'             => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            'product_websites' => $productWebsiteClosure,
            'categories'         => $productCategoryClosure,
            'name'              => 'Simple Product %s',
            'short_description' => 'Short simple product description %s',
            'weight'            => 1,
            'description'       => 'Full simple product Description %s',
            'sku'               => 'product_dynamic_%s',
            'price'             => 10,
            'visibility'        => 'Catalog, Search',
            'product_online'            => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
            'tax_class_name'      => 'Taxable Goods',
            /**
             * actually it saves without stock data, but by default system won't show on the
             * frontend products out of stock
             */
            'is_in_stock'                   => 1,
            'qty'                           => 100500,
            'out_of_stock_qty'            => 'Use Config',
            'allow_backorders'         => 'Use Config',
            'min_cart_qty'       => 'Use Config',
            'max_cart_qty'       => 'Use Config',
            'notify_on_stock_below'   => 'Use Config',
            'manage_stock'       => 'Use Config',
            'qty_increments'     => 'Use Config',
            'enable_qty_incremements'     => 'Use Config',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating simple products';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [
            'simple_products' => 'Simple products'
        ];
    }
}
