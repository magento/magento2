<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\Exception\ValidatorException;
use Magento\Setup\Model\DataGenerator;
use Magento\Setup\Model\FixtureGenerator\ConfigurableProductGenerator;
use Magento\Setup\Model\FixtureGenerator\ProductGenerator;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate configurable products based on profile configuration
 * Generated configurable options are not displayed individually in catalog
 * Support one of two formats:
 * 1. Distributed per Default and pre-defined attribute sets (@see setup/performance-toolkit/config/attributeSets.xml)
 * <configurable_products>{products amount}</configurable_products>
 *
 * 2.1 Generate products based on existing attribute set:
 * <configurable_products>
 *     <config>
 *          <attributeSet>{Existing attribute set name}</attributeSet>
 *          <sku>{Configurable sku pattern with %s}</sku>
 *          <products>{Amount of configurable products}</products>
 *          <category>[{Category Name}]</category> By default category name from CategoriesFixture will be used
 *          <swatches>color|image</swatches> Type of Swatch attribute
 *     </config>
 * </configurable_products>
 *
 * 2.2 Generate products based on dynamically created attribute set with specified amount of attributes and options
 * <configurable_products>
 *     <config>
 *          <attributes>{Amount of attributes in configurable product}</attributes>
 *          <options>{Amount of options per attribute}</options>
 *          <sku>{Configurable sku pattern with %s}</sku>
 *          <products>{Amount of configurable products}</products>
 *          <category>[{Category Name}]</category> By default category name from CategoriesFixture will be used
 *          <swatches>color|image</swatches> Type of Swatch attribute
 *     </config>
 * </configurable_products>
 *
 * 2.3 Generate products based on dynamically created attribute set with specified configuration per each attribute
 * <configurable_products> <!-- Configurable product -->
 *      <config>
 *          <attributes>
 *              <!-- Configuration for a first attribute -->
 *              <attribute>
 *                  <options>{Amount of options per attribute}</options>
 *                  <swatches>color|image</swatches> Type of Swatch attribute
 *              </attribute>
 *              <!-- Configuration for a second attribute -->
 *              <attribute>
 *                  <options>{Amount of options per attribute}</options>
 *              </attribute>
 *          </attributes>
 *          <sku>{Configurable sku pattern with %s}</sku>
 *          <products>{Amount of configurable products}</products>
 *      </config>
 * </configurable_products>
 *
 * Products will be uniformly distributed per categories and websites
 * If node "assign_entities_to_all_websites" from profile is set to "1" then products will be assigned to all websites
 *
 * @see setup/performance-toolkit/profiles/ce/small.xml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableProductsFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 50;

    /**
     * @var array
     */
    private $searchConfig;

    /**
     * @var DataGenerator
     */
    private $dataGenerator;

    /**
     * @var AttributeSet\AttributeSetFixture
     */
    private $attributeSetsFixture;

    /**
     * @var AttributeSet\Pattern
     */
    private $attributePattern;

    /**
     * @var ProductGenerator
     */
    private $productGenerator;

    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var ConfigurableProductGenerator
     */
    private $configurableProductGenerator;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var int
     */
    private $productStartIndex;

    /**
     * @var ProductsAmountProvider
     */
    private $productsAmountProvider;

    /**
     * @var CategoryResolver
     */
    private $categoryResolver;

    /**
     * @var WebsiteCategoryProvider
     */
    private $websiteCategoryProvider;

    /**
     * @var PriceProvider
     */
    private $priceProvider;

    /**
     * @var \Magento\Setup\Fixtures\AttributeSet\SwatchesGenerator
     */
    private $swatchesGenerator;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param FixtureModel $fixtureModel
     * @param AttributeSet\AttributeSetFixture $attributeSetsFixture
     * @param AttributeSet\Pattern $attributePattern
     * @param ProductGenerator $productGenerator
     * @param CollectionFactory $attributeCollectionFactory
     * @param ConfigurableProductGenerator $configurableProductGenerator
     * @param ProductCollectionFactory $collectionFactory
     * @param ProductsAmountProvider $productsAmountProvider
     * @param CategoryResolver $categoryResolver
     * @param WebsiteCategoryProvider $websiteCategoryProvider
     * @param PriceProvider $priceProvider
     * @param AttributeSet\SwatchesGenerator $swatchesGenerator
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FixtureModel $fixtureModel,
        \Magento\Setup\Fixtures\AttributeSet\AttributeSetFixture $attributeSetsFixture,
        \Magento\Setup\Fixtures\AttributeSet\Pattern $attributePattern,
        ProductGenerator $productGenerator,
        CollectionFactory $attributeCollectionFactory,
        ConfigurableProductGenerator $configurableProductGenerator,
        ProductCollectionFactory $collectionFactory,
        ProductsAmountProvider $productsAmountProvider,
        CategoryResolver $categoryResolver,
        WebsiteCategoryProvider $websiteCategoryProvider,
        PriceProvider $priceProvider,
        \Magento\Setup\Fixtures\AttributeSet\SwatchesGenerator $swatchesGenerator,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        parent::__construct($fixtureModel);
        $this->attributeSetsFixture = $attributeSetsFixture;
        $this->attributePattern = $attributePattern;
        $this->productGenerator = $productGenerator;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->configurableProductGenerator = $configurableProductGenerator;
        $this->productCollectionFactory = $collectionFactory;
        $this->productsAmountProvider = $productsAmountProvider;
        $this->categoryResolver = $categoryResolver;
        $this->websiteCategoryProvider = $websiteCategoryProvider;
        $this->priceProvider = $priceProvider;
        $this->swatchesGenerator = $swatchesGenerator;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function execute()
    {
        if (!$this->fixtureModel->getValue('configurable_products', [])) {
            return;
        }
        $simpleProductsCount = $this->fixtureModel->getValue('simple_products', 0);
        $maxAmountOfWordsDescription = $this->getSearchConfigValue('max_amount_of_words_description');
        $maxAmountOfWordsShortDescription = $this->getSearchConfigValue('max_amount_of_words_short_description');
        $minAmountOfWordsDescription = $this->getSearchConfigValue('min_amount_of_words_description');
        $minAmountOfWordsShortDescription = $this->getSearchConfigValue('min_amount_of_words_short_description');

        foreach ($this->getConfigurableProductConfig() as $configurableConfig) {
            $configurableSku = $configurableConfig['sku'];
            $productAmount = $this->productsAmountProvider->getAmount(
                $configurableConfig['products'],
                $configurableSku
            );
            if (!$productAmount) {
                continue;
            }
            $searchTerms = $this->getSearchTerms();
            $shortDescriptionClosure = $this->getDescriptionClosure(
                $searchTerms,
                $simpleProductsCount,
                $productAmount,
                $maxAmountOfWordsShortDescription,
                $minAmountOfWordsShortDescription,
                'shortDescription'
            );
            $descriptionClosure = $this->getDescriptionClosure(
                $searchTerms,
                $simpleProductsCount,
                $productAmount,
                $maxAmountOfWordsDescription,
                $minAmountOfWordsDescription,
                'description'
            );
            $variationCount = $configurableConfig['variationCount'];
            $attributeSet = $configurableConfig['attributeSet'];
            $variationSkuClosure = function ($productId, $entityNumber) use ($configurableSku, $variationCount) {
                $variationIndex = $this->getConfigurableVariationIndex($entityNumber, $variationCount);
                $productId = $this->getConfigurableProductIndex($entityNumber, $variationCount);

                return sprintf($this->getConfigurableOptionSkuPattern($configurableSku), $productId, $variationIndex);
            };
            $fixture = [
                'name' => $variationSkuClosure,
                'sku' => $variationSkuClosure,
                'price' => function ($index, $entityNumber) {
                    return $this->priceProvider->getPrice($entityNumber);
                },
                'website_ids' => function ($index, $entityNumber) use ($variationCount) {
                    $configurableIndex = $this->getConfigurableProductIndex($entityNumber, $variationCount);

                    return $this->websiteCategoryProvider->getWebsiteIds($configurableIndex);
                },
                'attribute_set_id' => $attributeSet['id'],
                'additional_attributes' => $this->getAdditionalAttributesClosure(
                    $attributeSet['attributes'],
                    $variationCount
                ),
                'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
            ];
            $this->productGenerator->generate($productAmount * $variationCount, $fixture);

            $skuClosure = function ($productId, $entityNumber) use ($configurableSku) {
                return sprintf($configurableSku, $entityNumber + $this->getNewProductStartIndex());
            };
            $fixture = [
                '_variation_sku_pattern' => $this->getFirstVariationSkuPattern($configurableConfig),
                '_attributes_count' => count($attributeSet['attributes']),
                '_variation_count' => $variationCount,
                '_attributes' => $attributeSet['attributes'],
                'type_id' => Configurable::TYPE_CODE,
                'name' => $skuClosure,
                'sku' => $skuClosure,
                'description' => $descriptionClosure,
                'short_description' => $shortDescriptionClosure,
                'attribute_set_id' => $attributeSet['id'],
                'website_ids' => $this->getConfigurableWebsiteIdsClosure(),
                'category_ids' => $configurableConfig['category'],
                'meta_keyword' => $skuClosure,
                'meta_title' => $skuClosure,
            ];

            $this->configurableProductGenerator->generate($productAmount, $fixture);
        }
    }

    /**
     * @return \Closure
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function getConfigurableWebsiteIdsClosure()
    {
        return function ($index, $entityNumber) {
            return $this->websiteCategoryProvider->getWebsiteIds($entityNumber + $this->getNewProductStartIndex());
        };
    }

    /**
     * Get product distribution per attribute sets for default attribute sets
     *
     * @param array $defaultAttributeSets
     * @param int $configurableProductsCount
     * @return array
     */
    private function getDefaultAttributeSetsConfig(array $defaultAttributeSets, $configurableProductsCount)
    {
        $attributeSetClosure = function ($index) use ($defaultAttributeSets) {
            $attributeSetAmount = count(array_keys($defaultAttributeSets));
            mt_srand($index);

            return $attributeSetAmount > ($index - 1) % (int)$this->fixtureModel->getValue('categories', 30)
                ? array_keys($defaultAttributeSets)[mt_rand(0, $attributeSetAmount - 1)]
                : 'Default';
        };
        $productsPerSet = [];
        for ($i = 1; $i <= $configurableProductsCount; $i++) {
            $attributeSet = $attributeSetClosure($i);
            if (!isset($productsPerSet[$attributeSet])) {
                $productsPerSet[$attributeSet] = 0;
            }
            $productsPerSet[$attributeSet]++;
        }
        $configurableConfig = [];
        foreach ($defaultAttributeSets as $attributeSetName => $attributeSet) {
            $skuSuffix = $attributeSetName === 'Default' ? '' : ' - ' . $attributeSetName;
            $configurableConfig[] = [
                'attributeSet' => $attributeSetName,
                'products' => $productsPerSet[$attributeSetName],
                'sku' => 'Configurable Product %s' . $skuSuffix,
            ];
        }

        return $configurableConfig;
    }

    /**
     * Get sku pattern in format "{configurable-sku}{configurable-index}-option 1" for get associated product ids
     *
     * @param array $configurableConfig
     * @see \Magento\Setup\Model\FixtureGenerator\ConfigurableProductTemplateGenerator
     * @return string
     */
    private function getFirstVariationSkuPattern($configurableConfig)
    {
        $productIndex = $this->getConfigurableProductIndex(0, $configurableConfig['variationCount']);

        return sprintf($this->getConfigurableOptionSkuPattern($configurableConfig['sku']), $productIndex, 1);
    }

    /**
     * Get start product index which used in product name, sku, url generation
     *
     * @return int
     */
    private function getNewProductStartIndex()
    {
        if (null === $this->productStartIndex) {
            $this->productStartIndex = $this->productCollectionFactory->create()
                ->addFieldToFilter('type_id', Configurable::TYPE_CODE)
                ->getSize() + 1;
        }

        return $this->productStartIndex;
    }

    /**
     * Get configurable product index number
     *
     * @param int $entityNumber
     * @param int $variationCount
     * @return float
     */
    private function getConfigurableProductIndex($entityNumber, $variationCount)
    {
        return floor($entityNumber / $variationCount) + $this->getNewProductStartIndex();
    }

    /**
     * Get configurable variation index number
     *
     * @param int $entityNumber
     * @param int $variationCount
     * @return float
     */
    private function getConfigurableVariationIndex($entityNumber, $variationCount)
    {
        return $entityNumber % $variationCount + 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating configurable products';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     * @throws ValidatorException
     */
    public function printInfo(OutputInterface $output)
    {
        if (!$this->fixtureModel->getValue('configurable_products', [])) {
            return;
        }

        $configurableProductConfig = $this->prepareConfigurableConfig(
            $this->getDefaultAttributeSetsWithAttributes()
        );
        $generalAmount = array_sum(array_column($configurableProductConfig, 'products'));

        $output->writeln(sprintf('<info> |- Configurable products: %s</info>', $generalAmount));
    }

    /**
     * Gen default attribute sets with attributes
     * @see config/attributeSets.xml
     *
     * @return array
     */
    private function getDefaultAttributeSetsWithAttributes()
    {
        $attributeSets = $this->fixtureModel->getValue('attribute_sets', null);
        $attributeSetData = [];

        if ($attributeSets !== null && array_key_exists('attribute_set', $attributeSets)) {
            foreach ($attributeSets['attribute_set'] as $attributeSet) {
                $attributesData = array_key_exists(0, $attributeSet['attributes']['attribute'])
                    ? $attributeSet['attributes']['attribute'] : [$attributeSet['attributes']['attribute']];
                $attributes = [];
                foreach ($attributesData as $attributeData) {
                    $values = [];
                    $optionsData = array_key_exists(0, $attributeData['options']['option'])
                        ? $attributeData['options']['option'] : [$attributeData['options']['option']];
                    foreach ($optionsData as $optionData) {
                        $values[] = $optionData['label'];
                    }

                    $attributes[] = ['name' => $attributeData['attribute_code'], 'values' => $values];
                }
                $attributeSetData[$attributeSet['name']] = [
                    'name' => $attributeSet['name'],
                    'attributes' => $attributes
                ];
            }
        }

        $attributeOptions = range(1, $this->getConfigurableProductsVariationsValue());
        array_walk(
            $attributeOptions,
            function (&$item, $key) {
                $item = 'option ' . ($key + 1);
            }
        );
        $attributeSetData['Default'] = [
            'name' => 'Default',
            'attributes' => [
                [
                    'name' => 'configurable_variation',
                    'values' => $attributeOptions
                ]
            ]
        ];

        return $attributeSetData;
    }

    /**
     * Get configurable product configuration for generate products per attribute set
     *
     * @return array
     * @throws ValidatorException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getConfigurableProductConfig()
    {
        $defaultAttributeSets = $this->getDefaultAttributeSetsWithAttributes();
        $configurableProductConfig = $this->prepareConfigurableConfig($defaultAttributeSets);

        $configurableProductConfig = array_map(function ($config) {
            return array_merge(
                [
                    'attributeSet' => null,
                    'attributes' => null,
                    'options' => null,
                    'sku' => null,
                    'category' => null,
                    'swatches' => null,
                ],
                $config
            );
        }, $configurableProductConfig);

        $skuPull = [];
        foreach ($configurableProductConfig as $i => &$config) {
            $attributeSet = $config['attributeSet'];
            $attributes = $config['attributes'];
            $options = (int)$config['options'];
            if ($attributeSet && isset($defaultAttributeSets[$attributeSet])) {
                // process default attribute sets
                $attributeSet = $defaultAttributeSets[$attributeSet];
                $attributes = count($attributeSet['attributes']);
                $options = count($attributeSet['attributes'][0]['values']);
            } elseif (is_array($attributes)) {
                $attributeSet = $this->getCustomAttributeSet($attributes);
                $options = array_column($attributes, 'options');
                $attributes = count($attributes);
            } elseif ($attributes && $options) {
                $attributes  = (int)$attributes;
                // convert attributes and options to array for process custom attribute set creation
                $attributesData = array_map(function ($options) use ($config) {
                    return ['options' => $options, 'swatches' => $config['swatches']];
                }, array_fill(0, $attributes, $options));

                $attributeSet = $this->getCustomAttributeSet($attributesData);
            }

            // do not process if any required option is missed
            if (count(array_filter([$attributeSet, $attributes, $options])) !== 3) {
                unset($configurableProductConfig[$i]);
                continue;
            }

            $config['sku'] = $this->getConfigurableSkuPattern($config, $attributeSet['name']);
            $config['category'] = $this->getConfigurableCategory($config);
            $config['attributeSet'] = $this->convertAttributesToDBFormat($attributeSet);
            $config['attributes'] = $attributes;
            $config['options'] = $options;
            $config['variationCount'] = is_array($options) ? array_product($options) : pow($options, $attributes);
            $skuPull[] = $config['sku'];
        }

        if (count($skuPull) !== count(array_unique($skuPull))) {
            throw new ValidatorException(
                __('Sku pattern for configurable product must be unique per attribute set')
            );
        }

        return $configurableProductConfig;
    }

    /**
     * Prepare configuration. If amount of configurable products set in profile then return predefined attribute sets
     * else return configuration from profile
     *
     * @param array $defaultAttributeSets
     * @return array
     * @throws ValidatorException
     */
    private function prepareConfigurableConfig($defaultAttributeSets)
    {
        $configurableConfigs = $this->fixtureModel->getValue('configurable_products', []);
        $configurableConfigs = is_array($configurableConfigs) ? $configurableConfigs : (int)$configurableConfigs;
        if (is_int($configurableConfigs)) {
            $configurableConfigs = $this->getDefaultAttributeSetsConfig($defaultAttributeSets, $configurableConfigs);
        } elseif (isset($configurableConfigs['config'])) {
            if (!isset($configurableConfigs['config'][0])) {
                // in case when one variation is passed
                $configurableConfigs = [$configurableConfigs['config']];
            } else {
                $configurableConfigs = $configurableConfigs['config'];
            }

            foreach ($configurableConfigs as &$config) {
                if (isset($config['attributes']) && is_array($config['attributes'])) {
                    if (!isset($config['attributes']['attribute'][0])) {
                        $config['attributes'] = [$config['attributes']['attribute']];
                    } else {
                        $config['attributes'] = $config['attributes']['attribute'];
                    }
                }
            }
        } else {
            throw new ValidatorException(__('Configurable product config is invalid'));
        }

        return $configurableConfigs;
    }

    /**
     * @param array $config
     * @return \Closure
     */
    private function getConfigurableCategory($config)
    {
        if (isset($config['category'])) {
            return function ($index, $entityNumber) use ($config) {
                $websiteClosure = $this->getConfigurableWebsiteIdsClosure();
                $websites = $websiteClosure($index, $entityNumber);

                return $this->categoryResolver->getCategory(
                    array_shift($websites),
                    $config['category']
                );
            };
        } else {
            return function ($index, $entityNumber) {
                return $this->websiteCategoryProvider->getCategoryId($entityNumber);
            };
        }
    }

    /**
     * @param array $config
     * @param string $attributeSetName
     * @return string
     */
    private function getConfigurableSkuPattern($config, $attributeSetName)
    {
        $sku = isset($config['sku']) ? $config['sku'] : null;
        if (!$sku) {
            $sku = 'Configurable Product ' . $attributeSetName . ' %s';
        }
        if (false === strpos($sku, '%s')) {
            $sku .= ' %s';
        }

        return $sku;
    }

    /**
     * Provide attribute set based on attributes configuration
     *
     * @param array $attributes
     * @return array
     */
    private function getCustomAttributeSet(array $attributes)
    {
        $attributeSetHash = crc32($this->serializer->serialize($attributes));
        $attributeSetName = sprintf('Dynamic Attribute Set %s', $attributeSetHash);

        $pattern = $this->attributePattern->generateAttributeSet(
            $attributeSetName,
            count($attributes),
            array_column($attributes, 'options'),
            function ($index, $attribute) use ($attributeSetName, $attributes, $attributeSetHash) {
                $swatch = [];
                $attributeCode = substr(
                    sprintf('ca_%s_%s', $index, $attributeSetHash),
                    0,
                    Attribute::ATTRIBUTE_CODE_MAX_LENGTH
                );
                $data = [
                    'attribute_code' => $attributeCode,
                    'frontend_label' => 'Dynamic Attribute ' . $attributeCode,
                ];

                if (isset($attributes[$index - 1]['swatches'])) {
                    $data['is_visible_in_advanced_search'] = 1;
                    $data['is_searchable'] = 1;
                    $data['is_filterable'] = 1;
                    $data['is_filterable_in_search'] = 1;
                    $data['used_in_product_listing'] = 1;

                    $swatch = $this->swatchesGenerator->generateSwatchData(
                        (int) $attributes[$index-1]['options'],
                        $attributeSetName . $index,
                        $attributes[$index-1]['swatches']
                    );
                }

                return array_replace_recursive(
                    $attribute,
                    $data,
                    $swatch
                );
            }
        );

        return $this->attributeSetsFixture->createAttributeSet($pattern);
    }

    /**
     * @return array
     */
    private function getSearchConfig()
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
    private function getSearchConfigValue($name)
    {
        return $this->getSearchConfig() === null
            ? 0 : ($this->getSearchConfig()[$name] === null ? 0 : $this->getSearchConfig()[$name]);
    }

    /**
     * @return array
     */
    private function getSearchTerms()
    {
        $searchTerms = $this->fixtureModel->getValue('search_terms', null);
        if ($searchTerms !== null) {
            $searchTerms = array_key_exists(0, $searchTerms['search_term'])
                ? $searchTerms['search_term'] : [$searchTerms['search_term']];
        }
        return $searchTerms;
    }

    /**
     * Get configurable products variations value.
     *
     * @return int
     */
    private function getConfigurableProductsVariationsValue()
    {
        return $this->fixtureModel->getValue('configurable_products_variation', 3);
    }

    /**
     * Get additional attributes closure.
     *
     * @param array $attributes
     * @param int $variationCount
     * @return \Closure
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function getAdditionalAttributesClosure(array $attributes, $variationCount)
    {
        $optionsPerAttribute = array_map(function ($attr) {
            return count($attr['values']);
        }, $attributes);
        $variationsMatrix = $this->generateVariationsMatrix(count($attributes), $optionsPerAttribute);

        return function ($attributeSetId, $index, $entityNumber) use ($attributes, $variationCount, $variationsMatrix) {
            $variationIndex = $this->getConfigurableVariationIndex($entityNumber, $variationCount) - 1;
            if (isset($variationsMatrix[$variationIndex])) {
                $tempProductData = [];
                foreach ($variationsMatrix[$variationIndex] as $attributeIndex => $optionIndex) {
                    $attributeCode = $attributes[$attributeIndex]['name'];
                    $option = $attributes[$attributeIndex]['values'][$optionIndex];
                    $tempProductData[$attributeCode] = $option;
                }
                return $tempProductData;
            }

            return [];
        };
    }

    /**
     * Generates matrix of all possible variations.
     * @param int $attributesPerSet
     * @param int $optionsPerAttribute
     * @return array
     */
    private function generateVariationsMatrix($attributesPerSet, $optionsPerAttribute)
    {
        $variationsMatrix = null;
        for ($i = 0; $i < $attributesPerSet; $i++) {
            $variationsMatrix[] = range(0, $optionsPerAttribute[$i] - 1);
        }
        return $this->generateVariations($variationsMatrix);
    }

    /**
     * Build all possible variations based on attributes and options count.
     * @param array|null $variationsMatrix
     * @return array
     */
    private function generateVariations($variationsMatrix)
    {
        if (!$variationsMatrix) {
            return [[]];
        }
        $set = array_shift($variationsMatrix);
        $subset = $this->generateVariations($variationsMatrix);
        $result = [];
        foreach ($set as $value) {
            foreach ($subset as $subValue) {
                array_unshift($subValue, $value);
                $result[] = $subValue;
            }
        }
        return $result;
    }

    /**
     * Get configurable option sku pattern.
     *
     * @param string $skuPattern
     * @return string
     */
    private function getConfigurableOptionSkuPattern($skuPattern)
    {
        return $skuPattern . ' - option %s';
    }

    /**
     * @param array|null $searchTerms
     * @param int $simpleProductsCount
     * @param int $configurableProductsCount
     * @param int $maxAmountOfWordsDescription
     * @param int $minAmountOfWordsDescription
     * @param string $descriptionPrefix
     * @return \Closure
     */
    private function getDescriptionClosure(
        $searchTerms,
        $simpleProductsCount,
        $configurableProductsCount,
        $maxAmountOfWordsDescription,
        $minAmountOfWordsDescription,
        $descriptionPrefix = 'description'
    ) {
        if (null === $this->dataGenerator) {
            $fileName = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'dictionary.csv';
            $this->dataGenerator = new DataGenerator(realpath($fileName));
        }

        return function ($index) use (
            $searchTerms,
            $simpleProductsCount,
            $configurableProductsCount,
            $maxAmountOfWordsDescription,
            $minAmountOfWordsDescription,
            $descriptionPrefix
        ) {
            $count = $searchTerms === null
                ? 0
                : round(
                    $searchTerms[$index % count($searchTerms)]['count'] * (
                        $configurableProductsCount / ($simpleProductsCount + $configurableProductsCount)
                    )
                );
            mt_srand($index);
            return $this->dataGenerator->generate(
                $minAmountOfWordsDescription,
                $maxAmountOfWordsDescription,
                $descriptionPrefix . '-' . $index
            ) .
            ($index <= ($count * count($searchTerms)) ? ' ' .
            $searchTerms[$index % count($searchTerms)]['term'] : '');
        };
    }

    /**
     * Convert attribute set data to db format for use in simple product generation
     *
     * @param array $attributeSet
     * @return array
     */
    private function convertAttributesToDBFormat(array $attributeSet)
    {
        $attributeSetName = $attributeSet['name'];
        $attributeSetId = null;
        $attributes = [];
        foreach ($attributeSet['attributes'] as $attributeData) {
            $attributeCollection = $this->attributeCollectionFactory->create();

            $attributeCollection->setAttributeSetFilterBySetName($attributeSetName, Product::ENTITY);
            $attributeCollection->addFieldToFilter(
                'attribute_code',
                $attributeData['name']
            );
            /** @var Attribute $attribute */
            foreach ($attributeCollection as $attribute) {
                $attributeSetId = $attribute->getAttributeSetId();
                $values = [];
                $options = $attribute->getOptions();
                foreach ($options ?: [] as $option) {
                    if ($option->getValue()) {
                        $values[] = $option->getValue();
                    }
                }
                $attributes[] =
                    [
                        'name' => $attribute->getAttributeCode(),
                        'id' => $attribute->getAttributeId(),
                        'values' => $values
                    ];
            }
        }

        return ['id' => $attributeSetId, 'name' => $attributeSetName, 'attributes' => $attributes];
    }
}
