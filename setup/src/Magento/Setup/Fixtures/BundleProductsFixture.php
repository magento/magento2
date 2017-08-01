<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Generate bundle products based on profile configuration
 * Generated bundle selections are not displayed individually in catalog
 * Support the following format:
 * <bundle_products>{products amount}</bundle_products>
 * <bundle_products_options>{bundle product options amount}</bundle_products_options>
 * <bundle_products_variation>{amount of simple products per each option}</bundle_products_variation>
 *
 * Products will be uniformly distributed per categories and websites
 * If node "assign_entities_to_all_websites" from profile is set to "1" then products will be assigned to all websites
 *
 * @see setup/performance-toolkit/profiles/ce/small.xml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.2.0
 */
class BundleProductsFixture extends Fixture
{
    /**
     * Bundle sku pattern with entity number and suffix. Suffix equals "{options}-{variations_per_option}"
     */
    const SKU_PATTERN = 'Bundle Product %s - %s';

    /**
     * @var int
     * @since 2.2.0
     */
    protected $priority = 42;

    /**
     * @var \Magento\Setup\Model\FixtureGenerator\ProductGenerator
     * @since 2.2.0
     */
    private $productGenerator;

    /**
     * @var \Magento\Setup\Model\FixtureGenerator\BundleProductGenerator
     * @since 2.2.0
     */
    private $bundleProductGenerator;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     * @since 2.2.0
     */
    private $productCollectionFactory;

    /**
     * @var int
     * @since 2.2.0
     */
    private $productStartIndex;

    /**
     * @var ProductsAmountProvider
     * @since 2.2.0
     */
    private $productsAmountProvider;

    /**
     * @var WebsiteCategoryProvider
     * @since 2.2.0
     */
    private $websiteCategoryProvider;

    /**
     * @var PriceProvider
     * @since 2.2.0
     */
    private $priceProvider;

    /**
     * @param FixtureModel $fixtureModel
     * @param \Magento\Setup\Model\FixtureGenerator\ProductGenerator $productGenerator
     * @param \Magento\Setup\Model\FixtureGenerator\BundleProductGenerator $bundleProductGenerator
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param ProductsAmountProvider $productsAmountProvider
     * @param WebsiteCategoryProvider $websiteCategoryProvider
     * @param PriceProvider $priceProvider
     * @since 2.2.0
     */
    public function __construct(
        FixtureModel $fixtureModel,
        \Magento\Setup\Model\FixtureGenerator\ProductGenerator $productGenerator,
        \Magento\Setup\Model\FixtureGenerator\BundleProductGenerator $bundleProductGenerator,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        ProductsAmountProvider $productsAmountProvider,
        WebsiteCategoryProvider $websiteCategoryProvider,
        PriceProvider $priceProvider
    ) {
        parent::__construct($fixtureModel);
        $this->productGenerator = $productGenerator;
        $this->bundleProductGenerator = $bundleProductGenerator;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productsAmountProvider = $productsAmountProvider;
        $this->websiteCategoryProvider = $websiteCategoryProvider;
        $this->priceProvider = $priceProvider;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.2.0
     */
    public function execute()
    {
        $bundlesAmount = $this->fixtureModel->getValue('bundle_products', 0);
        $bundleOptions = $this->fixtureModel->getValue('bundle_products_options', 1);
        $bundleProductsPerOption = $this->fixtureModel->getValue('bundle_products_variation', 10);
        $bundleOptionSuffix = $bundleOptions . '-' . $bundleProductsPerOption;
        $variationCount = $bundleOptions * $bundleProductsPerOption;
        $bundlesAmount = $this->productsAmountProvider->getAmount(
            $bundlesAmount,
            $this->getBundleSkuPattern($bundleOptionSuffix)
        );

        if (!$bundlesAmount) {
            return;
        }
        $variationSkuClosure = function ($productId, $entityNumber) use ($bundleOptionSuffix, $variationCount) {
            $productIndex = $this->getBundleProductIndex($entityNumber, $variationCount);
            $variationIndex = $this->getBundleVariationIndex($entityNumber, $variationCount);

            return sprintf($this->getBundleOptionItemSkuPattern($bundleOptionSuffix), $productIndex, $variationIndex);
        };
        $fixtureMap = [
            'name' => $variationSkuClosure,
            'sku' => $variationSkuClosure,
            'price' => function ($index, $entityNumber) {
                return $this->priceProvider->getPrice($entityNumber);
            },
            'website_ids' => function ($index, $entityNumber) use ($variationCount) {
                $configurableIndex = $this->getBundleProductIndex($entityNumber, $variationCount);

                return $this->websiteCategoryProvider->getWebsiteIds($configurableIndex);
            },
            'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
        ];
        $this->productGenerator->generate($bundlesAmount * $bundleOptions * $bundleProductsPerOption, $fixtureMap);

        $optionPriceType = [
            LinkInterface::PRICE_TYPE_FIXED,
            LinkInterface::PRICE_TYPE_PERCENT,
        ];
        $priceTypeClosure = function ($index) use ($optionPriceType) {
            return $optionPriceType[$index % count($optionPriceType)];
        };
        $skuClosure = function ($index, $entityNumber) use ($bundleOptionSuffix) {
            return sprintf(
                $this->getBundleSkuPattern($bundleOptionSuffix),
                $entityNumber + $this->getNewProductStartIndex()
            );
        };
        $fixtureMap = [
            '_bundle_options' => $bundleOptions,
            '_bundle_products_per_option' => $bundleProductsPerOption,
            '_bundle_variation_sku_pattern' => sprintf(
                $this->getBundleOptionItemSkuPattern($bundleOptionSuffix),
                $this->getNewProductStartIndex(),
                '%s'
            ),
            'type_id' => Type::TYPE_CODE,
            'name' => $skuClosure,
            'sku' => $skuClosure,
            'meta_title' => $skuClosure,
            'price' => function ($index) use ($priceTypeClosure) {
                return $priceTypeClosure($index) === LinkInterface::PRICE_TYPE_PERCENT
                    ? mt_rand(10, 90)
                    : $this->priceProvider->getPrice($index);
            },
            'priceType' => $priceTypeClosure,
            'website_ids' => function ($index, $entityNumber) {
                return $this->websiteCategoryProvider->getWebsiteIds($entityNumber + $this->getNewProductStartIndex());
            },
            'category_ids' => function ($index, $entityNumber) {
                return $this->websiteCategoryProvider->getCategoryId($entityNumber + $this->getNewProductStartIndex());
            },
        ];
        $this->bundleProductGenerator->generate($bundlesAmount, $fixtureMap);
    }

    /**
     * Get sku pattern for bundle product option item
     *
     * @param string $bundleOptionSuffix
     * @return string
     * @since 2.2.0
     */
    private function getBundleOptionItemSkuPattern($bundleOptionSuffix)
    {
        return $this->getBundleSkuPattern($bundleOptionSuffix) . ' - option %s';
    }

    /**
     * Get sku pattern for bundle product. Replace suffix pattern with passed value
     *
     * @param string $bundleOptionSuffix
     * @return string
     * @since 2.2.0
     */
    private function getBundleSkuPattern($bundleOptionSuffix)
    {
        return sprintf(self::SKU_PATTERN, '%s', $bundleOptionSuffix);
    }

    /**
     * Get start index for product number generation
     *
     * @return int
     * @since 2.2.0
     */
    private function getNewProductStartIndex()
    {
        if (null === $this->productStartIndex) {
            $this->productStartIndex = $this->productCollectionFactory->create()
                    ->addFieldToFilter('type_id', Type::TYPE_CODE)
                    ->getSize() + 1;
        }

        return $this->productStartIndex;
    }

    /**
     * Get bundle product index number
     *
     * @param int $entityNumber
     * @param int $variationCount
     * @return float
     * @since 2.2.0
     */
    private function getBundleProductIndex($entityNumber, $variationCount)
    {
        return floor($entityNumber / $variationCount) + $this->getNewProductStartIndex();
    }

    /**
     * Get bundle variation index number
     *
     * @param int $entityNumber
     * @param int $variationCount
     * @return float
     * @since 2.2.0
     */
    private function getBundleVariationIndex($entityNumber, $variationCount)
    {
        return $entityNumber % $variationCount + 1;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getActionTitle()
    {
        return 'Generating bundle products';
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function introduceParamLabels()
    {
        return [
            'bundle_products' => 'Bundle products',
        ];
    }
}
