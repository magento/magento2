<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Setup\Model\FixtureGenerator\ProductGenerator;
use Magento\Setup\Model\SearchTermDescriptionGeneratorFactory;

/**
 * Generate simple products based on profile configuration
 * Support the following format:
 * <simple_products>{products amount}</simple_products>
 * Products will be distributed per Default and pre-defined
 * attribute sets (@see setup/performance-toolkit/config/attributeSets.xml)
 *
 * If extra attribute set is specified in profile as: <product_attribute_sets>{sets amount}</product_attribute_sets>
 * then products also will be distributed per additional attribute sets
 *
 * Products will be uniformly distributed per categories and websites
 * If node "assign_entities_to_all_websites" from profile is set to "1" then products will be assigned to all websites
 *
 * @see setup/performance-toolkit/profiles/ce/small.xml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SimpleProductsFixture extends Fixture
{
    /**
     * Simple product sku pattern
     */
    const SKU_PATTERN = 'product_dynamic_%s';

    /**
     * @var int
     */
    protected $priority = 31;

    /**
     * @var array
     */
    private $descriptionConfig;

    /**
     * @var array
     */
    private $shortDescriptionConfig;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ProductGenerator
     */
    private $productGenerator;

    /**
     * @var int
     */
    private $defaultAttributeSetId;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    private $attributeCollectionFactory;

    /**
     * @var AttributeSetCollectionFactory
     */
    private $attributeSetCollectionFactory;

    /**
     * @var SearchTermDescriptionGeneratorFactory
     */
    private $descriptionGeneratorFactory;

    /**
     * @var ProductsAmountProvider
     */
    private $productsAmountProvider;

    /**
     * @var WebsiteCategoryProvider
     */
    private $websiteCategoryProvider;

    /**
     * @var PriceProvider
     */
    private $priceProvider;

    /**
     * @param FixtureModel $fixtureModel
     * @param ProductFactory $productFactory
     * @param ProductGenerator $productGenerator
     * @param CollectionFactory $attributeCollectionFactory
     * @param AttributeSetCollectionFactory $attributeSetCollectionFactory
     * @param SearchTermDescriptionGeneratorFactory $descriptionGeneratorFactory
     * @param WebsiteCategoryProvider $websiteCategoryProvider
     * @param ProductsAmountProvider $productsAmountProvider
     * @param PriceProvider $priceProvider
     * @internal param FixtureConfig $fixtureConfig
     */
    public function __construct(
        FixtureModel $fixtureModel,
        ProductFactory $productFactory,
        ProductGenerator $productGenerator,
        CollectionFactory $attributeCollectionFactory,
        AttributeSetCollectionFactory $attributeSetCollectionFactory,
        SearchTermDescriptionGeneratorFactory $descriptionGeneratorFactory,
        WebsiteCategoryProvider $websiteCategoryProvider,
        ProductsAmountProvider $productsAmountProvider,
        PriceProvider $priceProvider
    ) {
        parent::__construct($fixtureModel);
        $this->productFactory = $productFactory;
        $this->productGenerator = $productGenerator;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->descriptionGeneratorFactory = $descriptionGeneratorFactory;
        $this->productsAmountProvider = $productsAmountProvider;
        $this->websiteCategoryProvider = $websiteCategoryProvider;
        $this->priceProvider = $priceProvider;
    }

    /**
     * @inheritdoc
     */
    public function getActionTitle()
    {
        return 'Generating simple products';
    }

    /**
     * @inheritdoc
     */
    public function introduceParamLabels()
    {
        return [
            'simple_products' => 'Simple products'
        ];
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD)
     */
    public function execute()
    {
        $simpleProductsCount = $this->productsAmountProvider->getAmount(
            $this->fixtureModel->getValue('simple_products', 0),
            $this->getSkuPattern()
        );

        if (!$simpleProductsCount) {
            return;
        }

        $defaultAttributeSets = $this->getDefaultAttributeSets();
        $searchTermsConfig = $this->getSearchTerms();

        /** @var \Magento\Setup\Model\SearchTermDescriptionGenerator $descriptionGenerator */
        $descriptionGenerator = $this->descriptionGeneratorFactory->create(
            $this->getDescriptionConfig(),
            $searchTermsConfig,
            $simpleProductsCount,
            'Full simple product Description %s'
        );

        /** @var \Magento\Setup\Model\SearchTermDescriptionGenerator $shortDescriptionGenerator */
        $shortDescriptionGenerator = $this->descriptionGeneratorFactory->create(
            $this->getShortDescriptionConfig(),
            $searchTermsConfig,
            $simpleProductsCount,
            'Short simple product Description %s'
        );

        $additionalAttributeSets = $this->getAdditionalAttributeSets();
        $attributeSet = function ($index) use ($defaultAttributeSets, $additionalAttributeSets) {
            mt_srand($index);
            $attributeSetCount = count(array_keys($defaultAttributeSets));
            if ($attributeSetCount > (($index - 1) % (int)$this->fixtureModel->getValue('categories', 30))) {
                return array_keys($defaultAttributeSets)[mt_rand(0, count(array_keys($defaultAttributeSets)) - 1)];
            } else {
                $customSetsAmount = count($additionalAttributeSets);
                return $customSetsAmount
                    ? $additionalAttributeSets[$index % count($additionalAttributeSets)]['attribute_set_id']
                    : $this->getDefaultAttributeSetId();
            }
        };

        $additionalAttributes = function (
            $attributeSetId,
            $index
        ) use (
            $defaultAttributeSets,
            $additionalAttributeSets
        ) {
            $attributeValues = [];
            mt_srand($index);
            if (isset($defaultAttributeSets[$attributeSetId])) {
                foreach ($defaultAttributeSets[$attributeSetId] as $attributeCode => $values) {
                    $attributeValues[$attributeCode] = $values[mt_rand(0, count($values) - 1)];
                }
            }

            return $attributeValues;
        };

        $fixtureMap = [
            'name' => function ($productId) {
                return sprintf('Simple Product %s', $productId);
            },
            'sku' => function ($productId) {
                return sprintf($this->getSkuPattern(), $productId);
            },
            'price' => function ($index, $entityNumber) {
                return $this->priceProvider->getPrice($entityNumber);
            },
            'url_key' => function ($productId) {
                return sprintf('simple-product-%s', $productId);
            },
            'description' => function ($index) use ($descriptionGenerator) {
                return $descriptionGenerator->generate($index);
            },
            'short_description' => function ($index) use ($shortDescriptionGenerator) {
                return $shortDescriptionGenerator->generate($index);
            },
            'website_ids' => function ($index, $entityNumber) {
                return $this->websiteCategoryProvider->getWebsiteIds($index);
            },
            'category_ids' => function ($index, $entityNumber) {
                return $this->websiteCategoryProvider->getCategoryId($index);
            },
            'attribute_set_id' => $attributeSet,
            'additional_attributes' => $additionalAttributes,
            'status' => function () {
                return Status::STATUS_ENABLED;
            }
        ];
        $this->productGenerator->generate($simpleProductsCount, $fixtureMap);
    }

    /**
     * Get simple product sku pattern
     *
     * @return string
     */
    private function getSkuPattern()
    {
        return self::SKU_PATTERN;
    }

    /**
     * Get default attribute set id
     *
     * @return int
     */
    private function getDefaultAttributeSetId()
    {
        if (null === $this->defaultAttributeSetId) {
            $this->defaultAttributeSetId = (int)$this->productFactory->create()->getDefaultAttributeSetId();
        }

        return $this->defaultAttributeSetId;
    }

    /**
     * Get default attribute sets with attributes
     *
     * @see config/attributeSets.xml
     * @return array
     */
    private function getDefaultAttributeSets()
    {
        $attributeSets = $this->fixtureModel->getValue('attribute_sets', null);
        $attributes = [];

        if ($attributeSets !== null && array_key_exists('attribute_set', $attributeSets)) {
            foreach ($attributeSets['attribute_set'] as $attributeSet) {
                $attributesData = array_key_exists(0, $attributeSet['attributes']['attribute'])
                    ? $attributeSet['attributes']['attribute'] : [$attributeSet['attributes']['attribute']];

                $attributeCollection = $this->attributeCollectionFactory->create();

                $attributeCollection->setAttributeSetFilterBySetName($attributeSet['name'], Product::ENTITY);
                $attributeCollection->addFieldToFilter(
                    'attribute_code',
                    array_column($attributesData, 'attribute_code')
                );
                /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
                foreach ($attributeCollection as $attribute) {
                    $values = [];
                    $options = $attribute->getOptions();
                    foreach (($options ?: []) as $option) {
                        if ($option->getValue()) {
                            $values[] = $option->getValue();
                        }
                    }
                    $attributes[$attribute->getAttributeSetId()][$attribute->getAttributeCode()] = $values;
                }
            }
        }
        $attributes[$this->getDefaultAttributeSetId()] = [];

        return $attributes;
    }

    /**
     * Get search terms config which used for product description generation
     *
     * @return array
     */
    private function getSearchTerms()
    {
        $searchTerms = $this->fixtureModel->getValue('search_terms', []);
        if (!empty($searchTerms)) {
            $searchTerms = array_key_exists(0, $searchTerms['search_term'])
                ? $searchTerms['search_term'] : [$searchTerms['search_term']];
        }

        return $searchTerms;
    }

    /**
     * Get description config
     *
     * @return array
     */
    private function getDescriptionConfig()
    {
        if (null === $this->descriptionConfig) {
            $this->descriptionConfig = $this->readDescriptionConfig('description');
        }

        return $this->descriptionConfig;
    }

    /**
     * Get short description config
     *
     * @return array
     */
    private function getShortDescriptionConfig()
    {
        if (null === $this->shortDescriptionConfig) {
            $this->shortDescriptionConfig = $this->readDescriptionConfig('short-description');
        }

        return $this->shortDescriptionConfig;
    }

    /**
     * Get description config from fixture
     *
     * @param string $configSrc
     * @return array
     */
    private function readDescriptionConfig($configSrc)
    {
        $configData = $this->fixtureModel->getValue($configSrc, []);

        if (isset($configData['mixin']['tags'])) {
            $configData['mixin']['tags'] = explode('|', $configData['mixin']['tags']);
        }

        return $configData;
    }

    /**
     * Get additional attribute sets
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection[]
     */
    private function getAdditionalAttributeSets()
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $sets */
        $sets = $this->attributeSetCollectionFactory->create();
        $sets->addFieldToFilter('attribute_set_name', ['like' => AttributeSetsFixture::PRODUCT_SET_NAME . '%']);

        return $sets->getData();
    }
}
