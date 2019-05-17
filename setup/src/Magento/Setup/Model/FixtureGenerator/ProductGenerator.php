<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\FixtureGenerator;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory as StoreCollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

/**
 * Generate specified amount of products based on passed fixture
 * Fixture must return at least name and sku for new generated product:
 * [
 *      'name' => function ($entityId, $entityIndex) {return 'Product Name' . $entityIndex},
 *      'sku' => function ($entityId, $entityIndex) {return 'Product Sku' . $entityIndex},
 * ]
 *       And optional parameters (by default will be populated with default values)
 * [
 *      'attribute_set_id' => value or callback in format function ($entityId, $entityIndex) {return attribute_id}
 *      'additional_attributes' => callback in format function ($entityId, $entityIndex) {return [attribute => value]}
 *      'url_key' => callback in format function ($entityId, $entityIndex) {return url_key}
 *      'website_ids' => callback in format function ($entityId, $entityIndex) {return [website_id]}
 *      'status' => value or callback in format function ($entityId, $entityIndex) {return status}
 *      'price' => value or callback in format function ($entityId, $entityIndex) {return price}
 *      'description' => value or callback in format function ($entityId, $entityIndex) {return description}
 *      'short_description' => value or callback in format function ($entityId, $entityIndex) {return short_description}
 *      'category_ids' => callback in format function ($entityId, $entityIndex) {return category_ids}
 *      'type_id' => value or callback in format function ($entityId, $entityIndex) {return type_id}
 *      'meta_keyword' => value or callback in format function ($entityId, $entityIndex) {return meta_keyword}
 *      'meta_title' => value or callback in format function ($entityId, $entityIndex) {return meta_title}
 * ]
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductGenerator
{
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var UrlRewriteFactory
     */
    private $urlRewriteFactory;

    /**
     * @var StoreCollectionFactory
     */
    private $storeCollectionFactory;

    /**
     * @var array
     */
    private $categories = [];

    /**
     * @var array
     */
    private $storesPerWebsite = [];

    /**
     * @var EntityGeneratorFactory
     */
    private $entityGeneratorFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductTemplateGeneratorFactory
     */
    private $productTemplateGeneratorFactory;

    /**
     * @var array
     */
    private $productUrlSuffix = [];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $customTableMap;

    /**
     * @param ProductFactory $productFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param StoreCollectionFactory $storeCollectionFactory
     * @param EntityGeneratorFactory $entityGeneratorFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductTemplateGeneratorFactory $productTemplateGeneratorFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param array $customTableMap
     */
    public function __construct(
        ProductFactory $productFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        UrlRewriteFactory $urlRewriteFactory,
        StoreCollectionFactory $storeCollectionFactory,
        EntityGeneratorFactory $entityGeneratorFactory,
        StoreManagerInterface $storeManager,
        ProductTemplateGeneratorFactory $productTemplateGeneratorFactory,
        ScopeConfigInterface $scopeConfig,
        $customTableMap = []
    ) {
        $this->productFactory = $productFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->entityGeneratorFactory = $entityGeneratorFactory;
        $this->storeManager = $storeManager;
        $this->productTemplateGeneratorFactory = $productTemplateGeneratorFactory;
        $this->scopeConfig = $scopeConfig;
        $this->customTableMap = $customTableMap;
    }

    /**
     * Generate simple products
     *
     * @param int $products
     * @param array $fixtureMap
     * @return void
     */
    public function generate($products, $fixtureMap)
    {
        $this->initializeFixtureDefaultValues($fixtureMap);
        $attributeSets = [];
        // prepare attribute sets distribution for save products per attribute set
        for ($productNumber = 1; $productNumber <= $products; $productNumber++) {
            $attributeSetId = $this->getFixtureValue('attribute_set_id', $productNumber, $productNumber, $fixtureMap);
            if (!isset($attributeSets[$attributeSetId])) {
                $attributeSets[$attributeSetId] = 0;
            }
            $attributeSets[$attributeSetId]++;
        }

        $customTableMap = [
            'url_rewrite' => [
                'entity_id_field' => 'entity_id',
                'handler' => function ($productId, $entityNumber, $fixture) {
                    return $this->urlRewriteHandler($productId, $entityNumber, $fixture);
                },
            ],
            'catalog_category_product' => [
                'fields' => [
                    'category_id' => 'category_ids',
                ],
            ],
            'catalog_product_entity' => [
                'fields' => [
                    'attribute_set_id' => 'attribute_set_id',
                    'sku' => 'sku',
                ],
            ],
        ];
        $websiteIdsFixtures = $fixtureMap['website_ids'](1, 0);
        if (is_array($websiteIdsFixtures) && count($websiteIdsFixtures) === 1) {
            // Get website id from fixture in case when one site is assigned per product
            $customTableMap['catalog_product_website'] = [
                'fields' => [
                    'website_id' => 'website_ids',
                ]
            ];
        }
        $generator = $this->entityGeneratorFactory->create(
            [
                'entityType' => ProductInterface::class,
                'customTableMap' => array_merge($customTableMap, $this->customTableMap)
            ]
        );
        foreach ($attributeSets as $attributeSetId => $productsAmount) {
            $fixtureMap = array_merge($fixtureMap, ['attribute_set_id' => $attributeSetId]);
            $generator->generate(
                $this->productTemplateGeneratorFactory->create($fixtureMap),
                $productsAmount,
                function ($productNumber, $entityNumber) use ($attributeSetId, $fixtureMap) {
                    // add additional attributes to fixture for fulfill it during product generation
                    return array_merge(
                        $fixtureMap,
                        $fixtureMap['additional_attributes']($attributeSetId, $productNumber, $entityNumber)
                    );
                }
            );
        }
    }

    /**
     * Initialize fixture default values
     *
     * @param array $fixture
     * @return void
     */
    private function initializeFixtureDefaultValues(array &$fixture)
    {
        $defaultValues = [
            'attribute_set_id' => function () {
                return $this->productFactory->create()->getDefaultAttributeSetId();
            },
            'additional_attributes' => function () {
                return [];
            },
            'url_key' => function ($productId, $entityNumber) use ($fixture) {
                return strtolower(str_replace(' ', '-', $fixture['sku']($productId, $entityNumber)));
            },
            'website_ids' => function () {
                return $this->storeManager->getDefaultStoreView()->getWebsiteId();
            },
            'status' => Status::STATUS_ENABLED,
        ];
        foreach ($defaultValues as $fixtureKey => $value) {
            if (!isset($fixture[$fixtureKey])) {
                $fixture[$fixtureKey] = $value;
            }
        }
    }

    /**
     * Get fixture value
     *
     * @param string $fixtureKey
     * @param int $productId
     * @param int $entityNumber
     * @param array $fixtureMap
     * @return mixed|string
     */
    private function getFixtureValue($fixtureKey, $productId, $entityNumber, $fixtureMap)
    {
        $fixtureValue = isset($fixtureMap[$fixtureKey]) ? $fixtureMap[$fixtureKey] : null;
        return $fixtureValue ? $this->getBindValue($fixtureValue, $productId, $entityNumber) : '';
    }

    /**
     * Get bind value
     *
     * @param callable|mixed $fixtureValue
     * @param int $productId
     * @param int $entityNumber
     * @return mixed
     */
    private function getBindValue($fixtureValue, $productId, $entityNumber)
    {
        return is_callable($fixtureValue)
            ? $fixtureValue($productId, $entityNumber)
            : $fixtureValue;
    }

    /**
     * Handle generation sql query for url rewrite
     *
     * @param int $productId
     * @param int $entityNumber
     * @param array $fixtureMap
     * @return array
     * @throws LocalizedException
     */
    private function urlRewriteHandler($productId, $entityNumber, $fixtureMap)
    {
        $binds = [];
        $websiteIds = $fixtureMap['website_ids']($productId, $entityNumber);
        $websiteIds = is_array($websiteIds) ? $websiteIds : [$websiteIds];

        $bindPerStore = [];
        $requestPath = $this->getFixtureValue('url_key', $productId, $entityNumber, $fixtureMap);
        $targetPath = 'catalog/product/view/id/' . $productId;
        $urlRewrite = $this->urlRewriteFactory
            ->create()
            ->setRequestPath($requestPath)
            ->setTargetPath($targetPath)
            ->setEntityId($productId)
            ->setEntityType('product');
        $binds[] = $urlRewrite->toArray();

        if (isset($fixtureMap['category_ids']) && $this->isCategoryProductUrlRewriteGenerationEnabled()) {
            $categoryId = $fixtureMap['category_ids']($productId, $entityNumber);
            if (!isset($this->categories[$categoryId])) {
                $this->categories[$categoryId] = $this->categoryCollectionFactory
                    ->create()
                    ->addIdFilter($categoryId)
                    ->addAttributeToSelect('url_path')
                    ->getFirstItem()
                    ->getUrlPath();
            }
            $urlRewrite->setMetadata(['category_id' => $categoryId])
                ->setRequestPath($this->categories[$categoryId] . '/' . $requestPath)
                ->setTargetPath($targetPath . '/category/' . $categoryId);
            $binds[] = $urlRewrite->toArray();
        }

        foreach ($websiteIds as $websiteId) {
            if (!isset($this->storesPerWebsite[$websiteId])) {
                $this->storesPerWebsite[$websiteId] = $this->storeCollectionFactory
                    ->create()
                    ->addWebsiteFilter($websiteId)
                    ->getAllIds();
            }
            foreach ($binds as $bind) {
                foreach ($this->storesPerWebsite[$websiteId] as $storeId) {
                    $bindWithStore = $bind;
                    $bindWithStore['store_id'] = $storeId;
                    $bindWithStore['request_path'] .= $this->getUrlSuffix($storeId);
                    $bindPerStore[] = $bindWithStore;
                }
            }
        }

        return $bindPerStore;
    }

    /**
     * Get url suffix per store for product
     *
     * @param int $storeId
     * @return string
     */
    private function getUrlSuffix($storeId)
    {
        if (!isset($this->productUrlSuffix[$storeId])) {
            $this->productUrlSuffix[$storeId] = $this->scopeConfig->getValue(
                ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $this->productUrlSuffix[$storeId];
    }

    /**
     * Check config value of generate_category_product_rewrites
     *
     * @return bool
     */
    private function isCategoryProductUrlRewriteGenerationEnabled()
    {
        return (bool)$this->scopeConfig->getValue('catalog/seo/generate_category_product_rewrites');
    }
}
