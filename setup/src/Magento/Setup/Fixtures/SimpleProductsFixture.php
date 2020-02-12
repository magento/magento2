<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection as AttributeSetCollection;
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
     * @var Collection
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
     * @var int[]
     */
    private $additionalAttributeSetIds;

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

        $additionalAttributeSetIds = $this->getAdditionalAttributeSetIds();
        $attributeSet = function ($index) use ($defaultAttributeSets, $additionalAttributeSetIds) {
            // phpcs:ignore
            mt_srand($index);
            $attributeSetCount = count(array_keys($defaultAttributeSets));
            if ($attributeSetCount > (($index - 1) % (int)$this->fixtureModel->getValue('categories', 30))) {
                // phpcs:ignore Magento2.Security.InsecureFunction
                return array_keys($defaultAttributeSets)[mt_rand(0, count(array_keys($defaultAttributeSets)) - 1)];
            } else {
                $customSetsAmount = count($additionalAttributeSetIds);
                return $customSetsAmount
                    ? $additionalAttributeSetIds[$index % $customSetsAmount]
                    : $this->getDefaultAttributeSetId();
            }
        };

        $additionalAttributeValues = $this->getAdditionalAttributeValues();
        $additionalAttributes = function (
            $attributeSetId,
            $index
        ) use (
            $defaultAttributeSets,
            $additionalAttributeValues
        ) {
            $attributeValues = [];
            // phpcs:ignore
            mt_srand($index);
            $attributeValuesByAttributeSet = $defaultAttributeSets[$attributeSetId]
                ?? $additionalAttributeValues[$attributeSetId];
            if (!empty($attributeValuesByAttributeSet)) {
                foreach ($attributeValuesByAttributeSet as $attributeCode => $values) {
                    // phpcs:ignore Magento2.Security.InsecureFunction
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
     * Get default attribute sets with attributes.
     *
     * @return array
     * @see config/attributeSets.xml
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
                $attributes = $this->processAttributeValues($attributeCollection, $attributes);
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
     * Get additional attribute set ids.
     *
     * @return int[]
     */
    private function getAdditionalAttributeSetIds()
    {
        if (null === $this->additionalAttributeSetIds) {
            /** @var AttributeSetCollection $sets */
            $sets = $this->attributeSetCollectionFactory->create();
            $sets->addFieldToFilter(
                'attribute_set_name',
                ['like' => AttributeSetsFixture::PRODUCT_SET_NAME . '%']
            );
            $this->additionalAttributeSetIds = $sets->getAllIds();
        }

        return $this->additionalAttributeSetIds;
    }

    /**
     * Get values of additional attributes.
     *
     * @return array
     */
    private function getAdditionalAttributeValues(): array
    {
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->setAttributeSetsFilter($this->getAdditionalAttributeSetIds())
            ->addFieldToFilter('attribute_code', ['like' => 'attribute_set%']);
        $attributeCollection->getSelect()->columns(['entity_attribute.attribute_set_id']);

        return $this->processAttributeValues($attributeCollection);
    }

    /**
     * Maps attribute values by attribute set and attribute code.
     *
     * @param Collection $attributeCollection
     * @param array $attributes
     * @return array
     */
    private function processAttributeValues(
        Collection $attributeCollection,
        array $attributes = []
    ): array {
        /** @var Attribute $attribute */
        foreach ($attributeCollection as $attribute) {
            $values = [];
            $options = $attribute->getOptions() ?? [];
            $attributeSetId = $attribute->getAttributeSetId() ?? $this->getDefaultAttributeSetId();
            foreach ($options as $option) {
                if ($option->getValue()) {
                    $values[] = $option->getValue();
                }
            }
            $attributes[$attributeSetId][$attribute->getAttributeCode()] = $values;
        }

        return $attributes;
    }
}
