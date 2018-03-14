<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\BundleGraphQl\Model\Resolver\Products\Query;

use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;
use Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory;
use Magento\Bundle\Model\ResourceModel\Selection\Collection;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Retrieves simple product data for child products, and formats children data
 */
class BundleProductPostProcessor implements \Magento\Framework\GraphQl\Query\PostFetchProcessorInterface
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Bundle\Model\OptionFactory
     */
    private $bundleOption;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Bundle\Api\Data\OptionInterfaceFactory
     */
    private $optionFactory;

    /**
     * @var CollectionFactory
     */
    private $linkCollectionFactory;

    /**
     * @var LinkInterfaceFactory
     */
    private $linkFactory;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var string
     */
    private $productLinkField = "";

    /**
     * @var Product
     */
    private $productDataProvider;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Bundle\Model\OptionFactory $bundleOption
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory
     * @param CollectionFactory $linkCollectionFactory
     * @param LinkInterfaceFactory $linkFactory
     * @param MetadataPool $metadataPool
     * @param Product $productDataProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductResource $productResource
     * @param FormatterInterface $formatter
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        \Magento\Bundle\Model\OptionFactory $bundleOption,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory,
        CollectionFactory $linkCollectionFactory,
        LinkInterfaceFactory $linkFactory,
        MetadataPool $metadataPool,
        Product $productDataProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductResource $productResource,
        FormatterInterface $formatter,
        StoreManagerInterface $storeManager
    ) {
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->bundleOption = $bundleOption;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->optionFactory = $optionFactory;
        $this->linkCollectionFactory = $linkCollectionFactory;
        $this->linkFactory = $linkFactory;
        $this->metadataPool = $metadataPool;
        $this->productDataProvider = $productDataProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productResource = $productResource;
        $this->formatter = $formatter;
        $this->storeManager = $storeManager;
    }

    /**
     * Process all bundle product data, including adding simple product data and formatting relevant attributes.
     *
     * @param array $resultData
     * @return array
     */
    public function process(array $resultData) : array
    {
        $bundleProducts = [];
        $definedVars = get_defined_vars();
        foreach ($resultData as $product) {
            if ($product['type_id'] === Bundle::TYPE_CODE) {
                $bundleProducts[] = $product;
            }
        }

        if (empty($bundleProducts)) {
            return $resultData;
        }

        $optionsMap = $this->getBundleOptionsMap($bundleProducts);

        $optionsMap = $this->hydrateLinks($optionsMap);

        $childrenSkus = [];
        $bundleMap = [];
        $linkField = $this->getProductLinkField();
        /** @var OptionInterface[] $optionList */
        foreach ($optionsMap as $parentId => $optionList) {
            foreach ($resultData as $key => $product) {
                if ((int)$product[$linkField] === (int)$parentId) {
                    $resultData[$key]['bundle_product_options'] = $optionList;
                    foreach ($optionList as $option) {
                        if ($option->getSku()) {
                            $childrenSkus[] = $option->getSku();
                            $bundleMap[$product[ProductInterface::SKU]][] = $option->getSku();
                            continue;
                        }

                        foreach ($option->getProductLinks() as $link) {
                            if ($link->getSku()) {
                                $childrenSkus[] = $link->getSku();
                                $bundleMap[$product[ProductInterface::SKU]][] = $link->getSku();
                            }
                        }
                    }
                }
            }
        }

        if (empty($childrenSkus)) {
            return $resultData;
        }

        $this->searchCriteriaBuilder->addFilter(ProductInterface::SKU, $childrenSkus, 'in');
        $childProducts = $this->productDataProvider->getList($this->searchCriteriaBuilder->create());
        $resultData = $this->addChildData($childProducts->getItems(), $resultData, $bundleMap);

        return $resultData;

        $bundleMap = [];
        foreach ($resultData as $productKey => $product) {
            if (isset($product['type_id']) && $product['type_id'] === Bundle::TYPE_CODE) {
                if (isset($product['bundle_product_options'])) {
                    $bundleMap[$product['sku']] = [];
                    /** @var Option $option */
                    foreach ($product['bundle_product_options'] as $optionKey => $option) {
                        $resultData[$productKey]['items'][$optionKey]
                            = $option->getData();
                        /** @var LinkInterface $link */
                        foreach ($option['product_links'] as $link) {
                            $bundleMap[$product['sku']][] = $link->getSku();
                            $childrenSkus[] = $link->getSku();
                            $formattedLink = [
                                'product' => new GraphQlNoSuchEntityException(
                                    __('Bundled product not found')
                                ),
                                'price' => $link->getPrice(),
                                'position' => $link->getPosition(),
                                'id' => $link->getId(),
                                'qty' => (int)$link->getQty(),
                                'is_default' => (bool)$link->getIsDefault(),
                                'price_type' => $this->enumLookup->getEnumValueFromField(
                                    'PriceTypeEnum',
                                    (string)$link->getPriceType()
                                ) ?: 'DYNAMIC',
                                'can_change_quantity' => $link->getCanChangeQuantity()
                            ];
                            $resultData[$productKey]['items'][$optionKey]['options'][$link['sku']] = $formattedLink;
                        }
                    }
                }
            }
        }
    }

    /**
     * Retrieve bundle option collection with each option list assigned to a parent id key.
     *
     * Output format: [$parentId => [$option1, $option2...], ...]
     *
     * @param array $bundleProducts
     * @return array
     */
    private function getBundleOptionsMap(array $bundleProducts)
    {
        $uniqueKey = $this->getProductLinkField();
        /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
        $optionsCollection = $this->bundleOption->create()->getResourceCollection();
        // All products in collection will have same store id.
        $optionsCollection->joinValues($this->storeManager->getStore()->getId());
        foreach ($bundleProducts as $product) {
            $optionsCollection->setProductIdFilter($product[$uniqueKey]);
        }
        $optionsCollection->setPositionOrder();

        $this->extensionAttributesJoinProcessor->process($optionsCollection);
        if (empty($optionsCollection->getData())) {
            return [];
        }

        $optionsMap = [];
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($optionsCollection as $option) {
            /** @var OptionInterface $optionInterface */
            $optionInterface = $this->optionFactory->create();
            foreach ($bundleProducts as $product) {
                if ((int)$product[$uniqueKey] !== (int)$option->getParentId()) {
                    continue;
                }

                $this->dataObjectHelper->populateWithArray(
                    $optionInterface,
                    $option->getData(),
                    \Magento\Bundle\Api\Data\OptionInterface::class
                );
                $optionInterface->setOptionId($option->getOptionId())
                    ->setTitle($option->getTitle() === null ? $option->getDefaultTitle() : $option->getTitle())
                    ->setDefaultTitle($option->getDefaultTitle())
                    ->setSku($product['sku']);
                break;
            }

            if ($optionInterface->getOptionId() === null) {
                continue;
            }

            if (!isset($optionsMap[$option->getParentId()])) {
                $optionsMap[(int)$option->getParentId()] = [];
            }

            $optionsMap[(int)$option->getParentId()][] = $optionInterface;
        }

        return $optionsMap;
    }

    /**
     * Hydrate links for input options
     *
     * @param array $optionsMap
     * @return array
     */
    private function hydrateLinks(array $optionsMap)
    {
        $parentIds = [];
        $optionIds = [];
        foreach ($optionsMap as $parentId => $optionList) {
            $parentIds[] = $parentId;
            /** @var OptionInterface $option */
            foreach ($optionList as $option) {
                if (in_array($option->getOptionId(), $optionIds)) {
                    continue;
                }

                $optionsIds = [];
            }
        }

        /** @var Collection $linkCollection */
        $linkCollection = $this->linkCollectionFactory->create();
        $linkCollection->setOptionIdsFilter($optionsIds);
        $field = 'parent_product_id';
        foreach ($linkCollection->getSelect()->getPart('from') as $tableAlias => $data) {
            if ($data['tableName'] == $linkCollection->getTable('catalog_product_bundle_selection')) {
                $field = $tableAlias . '.' . $field;
            }
        }

        $linkCollection->getSelect()
            ->where($field . ' IN (?)', $parentIds);

        $productLinksMap = [];

        /** @var \Magento\Catalog\Model\Product $selection $link */
        foreach ($linkCollection as $link) {
            $selectionPriceType = $link->getSelectionPriceType();
            $selectionPrice = $link->getSelectionPriceValue();
            /** @var LinkInterface $productLink */
            $productLink = $this->linkFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $productLink,
                $link->getData(),
                \Magento\Bundle\Api\Data\LinkInterface::class
            );
            $productLink->setIsDefault($link->getIsDefault())
                ->setId($link->getSelectionId())
                ->setQty($link->getSelectionQty())
                ->setCanChangeQuantity($link->getSelectionCanChangeQty())
                ->setPrice($selectionPrice)
                ->setPriceType($selectionPriceType);
            if (!isset($productLinksMap[$productLink->getOptionId()])) {
                $productLinksMap[$productLink->getOptionId()] = [];
            }
            $productLinksMap[$productLink->getOptionId()][] = $productLink;
        }

        foreach ($productLinksMap as $optionId => $productLinkList) {
            foreach ($optionsMap as $parentId => $optionsList) {
                /** @var OptionInterface $option */
                foreach ($optionsList as $optionKey => $option) {
                    if ((int)$option->getOptionId() === (int)$optionId) {
                        $optionsMap[$parentId][$optionKey]->setProductLinks($productLinkList);
                    }
                }
            }
        }

        return $optionsMap;
    }

    /**
     * Format and add children product data to bundle product response items.
     *
     * @param \Magento\Catalog\Model\Product[] $childrenProducts
     * @param array $resultData
     * @param array $bundleMap Map of parent skus and their children they contain [$parentSku => [$child1, $child2...]]
     * @return array
     */
    private function addChildData(array $childrenProducts, array $resultData, array $bundleMap) : array
    {
        foreach ($childrenProducts as $childProduct) {
            $childData = $this->formatter->format($childProduct);
            foreach ($resultData as $productKey => $item) {
                if ($item['type_id'] === Bundle::TYPE_CODE
                    && in_array($childData['sku'], $bundleMap[$item['sku']])
                ) {
                    $categoryLinks = $this->productResource->getCategoryIds($childProduct);
                    foreach ($categoryLinks as $position => $categoryLink) {
                        $childData['category_links'][] = ['position' => $position, 'category_id' => $categoryLink];
                    }
//                    foreach ($item['bundle_product_options'] as $itemKey => $bundleItem) {
//                        foreach (array_keys($bundleItem['options']) as $optionKey) {
//                            if ($childData['sku'] === $optionKey) {
//                                $resultData[$productKey]['items'][$itemKey]['options'][$optionKey]['product']
//                                    = $childData;
//                                $resultData[$productKey]['items'][$itemKey]['options'][$optionKey]['label']
//                                    = $childData['name'];
//                            }
//                        }
//                    }
                }
            }
        }

        return $resultData;
    }

    /**
     * Get link field name for unique product identifier.
     *
     * @return string
     */
    private function getProductLinkField()
    {
        if (empty($this->productLinkField)) {
            $this->productLinkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        }

        return $this->productLinkField;
    }
}
