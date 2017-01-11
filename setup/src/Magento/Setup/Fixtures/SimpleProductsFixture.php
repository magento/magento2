<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Setup\Model\DataGenerator;
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
     * @var array
     */
    protected $searchConfig;

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function execute()
    {
        $simpleProductsCount = $this->fixtureModel->getValue('simple_products', 0);
        if (!$simpleProductsCount) {
            return;
        }
        $configurableProductsCount = $this->fixtureModel->getValue('configurable_products', 0);
        $maxAmountOfWordsDescription = $this->getSearchConfigValue('max_amount_of_words_description');
        $maxAmountOfWordsShortDescription = $this->getSearchConfigValue('max_amount_of_words_short_description');
        $minAmountOfWordsDescription = $this->getSearchConfigValue('min_amount_of_words_description');
        $minAmountOfWordsShortDescription = $this->getSearchConfigValue('min_amount_of_words_short_description');
        $searchTerms = $this->getSearchTerms();
        $attributes = $this->getAttributes();
        $this->fixtureModel->resetObjectManager();
        $result = $this->getCategoriesAndWebsites();
        $dataGenerator = new DataGenerator(realpath(__DIR__ . '/' . 'dictionary.csv'));

        $productWebsite = function ($index) use ($result) {
            return $result[$index % count($result)][0];
        };
        $productCategory = function ($index) use ($result) {
            return $result[$index % count($result)][1];
        };
        $shortDescription = function ($index) use (
            $searchTerms,
            $simpleProductsCount,
            $configurableProductsCount,
            $dataGenerator,
            $maxAmountOfWordsShortDescription,
            $minAmountOfWordsShortDescription
        ) {
            $count = $searchTerms === null
                ? 0
                : round(
                    $searchTerms[$index % count($searchTerms)]['count'] * (
                        $simpleProductsCount / ($simpleProductsCount + $configurableProductsCount)
                    )
                );
            return $dataGenerator->generate(
                $minAmountOfWordsShortDescription,
                $maxAmountOfWordsShortDescription
            ) . ($index <= ($count * count($searchTerms)) ? ' '
                . $searchTerms[$index % count($searchTerms)]['term'] : '');
        };
        $description = function ($index) use (
            $searchTerms,
            $simpleProductsCount,
            $configurableProductsCount,
            $dataGenerator,
            $maxAmountOfWordsDescription,
            $minAmountOfWordsDescription
        ) {
            $count = $searchTerms === null
                ? 0
                : round(
                    $searchTerms[$index % count($searchTerms)]['count'] * (
                        $simpleProductsCount / ($simpleProductsCount + $configurableProductsCount)
                    )
                );
            return $dataGenerator->generate(
                $minAmountOfWordsDescription,
                $maxAmountOfWordsDescription
            ) . ($index <= ($count * count($searchTerms)) ? ' '
                . $searchTerms[$index % count($searchTerms)]['term'] : '');
        };
        $price = function () {
            switch (mt_rand(0, 3)) {
                case 0:
                    return 9.99;
                case 1:
                    return 5;
                case 2:
                    return 1;
                case 3:
                    return mt_rand(1, 10000)/10;
            }
        };
        $attributeSet = function ($index) use ($attributes, $result) {
            mt_srand($index);
            return (count(array_keys($attributes)) > (($index - 1) % count($result))
                ? array_keys($attributes)[mt_rand(0, count(array_keys($attributes)) - 1)] : 'Default');
        };
        $additionalAttributes = function ($index) use ($attributes, $result) {
            $attributeValues = '';
            mt_srand($index);
            $attributeSetCode = (count(array_keys($attributes)) > (($index - 1) % count($result))
                ? array_keys($attributes)[mt_rand(0, count(array_keys($attributes)) - 1)] : 'Default');
            if ($attributeSetCode !== 'Default') {
                foreach ($attributes[$attributeSetCode] as $attribute) {
                    $attributeValues = $attributeValues . $attribute['name'] . "=" .
                        $attribute['values'][mt_rand(0, count($attribute['values']) - 1)] . ",";
                }
            }
            return trim($attributeValues, ",");
        };
        $generator = $this->fixtureModel->getObjectManager()->create(
            Generator::class,
            [
                'rowPattern' => $this->getPattern(
                    $productWebsite,
                    $productCategory,
                    $shortDescription,
                    $description,
                    $price,
                    $attributeSet,
                    $additionalAttributes
                ),
                'limit' => $simpleProductsCount
            ]
        );
        /** @var \Magento\ImportExport\Model\Import $import */
        $import = $this->fixtureModel->getObjectManager()->create(
            \Magento\ImportExport\Model\Import::class,
            [
                'data' => [
                    'entity' => 'catalog_product',
                    'behavior' => 'append',
                    'validation_strategy' => 'validation-stop-on-errors'
                ]
            ]
        );
        // it is not obvious, but the validateSource() will actually save import queue data to DB
        if (!$import->validateSource($generator)) {
            throw new \Exception($import->getFormatedLogTrace());
        }
        // this converts import queue into actual entities
        if (!$import->importSource()) {
            throw new \Exception($import->getFormatedLogTrace());
        }
    }

    /**
     * Get pattern for product import
     *
     * @param Closure|int|string $productWebsiteClosure
     * @param Closure|int|string $productCategoryClosure
     * @param Closure|int|string $shortDescriptionClosure
     * @param Closure|int|string $descriptionClosure
     * @param Closure|int|string $priceClosure
     * @param Closure|int|string $attributeSetClosure
     * @param Closure|int|string $additionalAttributesClosure
     * @return array
     */
    protected function getPattern(
        $productWebsiteClosure,
        $productCategoryClosure,
        $shortDescriptionClosure,
        $descriptionClosure,
        $priceClosure,
        $attributeSetClosure,
        $additionalAttributesClosure
    ) {
        return [
            'attribute_set_code'    => $attributeSetClosure,
            'additional_attributes' => $additionalAttributesClosure,
            'product_type'             => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            'product_websites' => $productWebsiteClosure,
            'categories'         => $productCategoryClosure,
            'name'              => 'Simple Product %s',
            'short_description' => $shortDescriptionClosure,
            'weight'            => 1,
            'description'       => $descriptionClosure,
            'sku'               => 'product_dynamic_%s',
            'price'             => $priceClosure,
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

    /**
     * @return array
     */
    protected function getAttributes()
    {
        $attributeSets = $this->getAttributeSets();
        $attributes = [];

        if ($attributeSets !== null && array_key_exists('attribute_set', $attributeSets)) {
            foreach ($attributeSets['attribute_set'] as $attributeSet) {
                $attributesData = array_key_exists(0, $attributeSet['attributes']['attribute'])
                    ? $attributeSet['attributes']['attribute'] : [$attributeSet['attributes']['attribute']];
                foreach ($attributesData as $attributeData) {
                    $values = [];
                    $optionsData = array_key_exists(0, $attributeData['options']['option'])
                        ? $attributeData['options']['option'] : [$attributeData['options']['option']];
                    foreach ($optionsData as $optionData) {
                        $values[] = $optionData['label'];
                    }
                    $attributes[$attributeSet['name']][] =
                        ['name' => $attributeData['attribute_code'], 'values' => $values];
                }
            }
        }
        return $attributes;
    }

    /**
     * @return array
     */
    protected function getCategoriesAndWebsites()
    {
        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->fixtureModel->getObjectManager()->create(\Magento\Store\Model\StoreManager::class);
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->fixtureModel->getObjectManager()->get(\Magento\Catalog\Model\Category::class);

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
        return array_values($result);
    }

    /**
     * @return array
     */
    protected function getSearchConfig()
    {
        if (!$this->searchConfig) {
            $this->searchConfig = $this->fixtureModel->getValue('search_config', null);
        }
        return $this->searchConfig;
    }

    /**
     * @param string $name
     * @return int|mixed
     */
    protected function getSearchConfigValue($name)
    {
        return $this->getSearchConfig() === null
            ? 0 : ($this->getSearchConfig()[$name] === null ? 0: $this->getSearchConfig()[$name]);
    }

    /**
     * @return array
     */
    protected function getSearchTerms()
    {
        $searchTerms = $this->fixtureModel->getValue('search_terms', null);
        if ($searchTerms !== null) {
            $searchTerms = array_key_exists(0, $searchTerms['search_term'])
                ? $searchTerms['search_term'] : [$searchTerms['search_term']];
        }
        return $searchTerms;
    }

    /**
     * Get attribute sets.
     *
     * @return array|null
     */
    private function getAttributeSets()
    {
        return $this->fixtureModel->getValue('attribute_sets', null);
    }
}
