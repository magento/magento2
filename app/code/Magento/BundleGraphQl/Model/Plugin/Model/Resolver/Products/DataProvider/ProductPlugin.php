<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\BundleGraphQl\Model\Plugin\Model\Resolver\Products\DataProvider;

use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;
use Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory;
use Magento\Bundle\Model\ResourceModel\Selection\Collection;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\LinkInterface;

/**
 * Fetch bundle product object and set necessary extension attributes for search result
 */
class ProductPlugin
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
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Bundle\Model\OptionFactory $bundleOption
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory
     * @param CollectionFactory $linkCollectionFactory
     * @param LinkInterfaceFactory $linkFactory
     */
    public function __construct(
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        \Magento\Bundle\Model\OptionFactory $bundleOption,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory,
        CollectionFactory $linkCollectionFactory,
        LinkInterfaceFactory $linkFactory
    ) {
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->bundleOption = $bundleOption;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->optionFactory = $optionFactory;
        $this->linkCollectionFactory = $linkCollectionFactory;
        $this->linkFactory = $linkFactory;
    }

    /**
     * Intercept GraphQLCatalog getList, and add any necessary bundle fields
     *
     * @param Product $subject
     * @param SearchResultsInterface $result
     * @return SearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(Product $subject, SearchResultsInterface $result) : SearchResultsInterface
    {
        $products = [];
        $productList = $result->getItems();
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($result->getItems() as $product) {
            if ($product->getTypeId() === Bundle::TYPE_CODE) {
                $products[] = $product;
            }
        }

        if (empty($products)) {
            return $result;
        }

        $options = $this->getOptionsCollectionByStoreId($products);

        $options = $this->hydrateLinks($options);

        foreach ($options as $parentId => $optionList) {
            foreach ($productList as $product) {
                if (!(int)$product->getId() === $parentId) {
                    continue;
                }
                $extensionAttributes = $product->getExtensionAttributes();
                $extensionAttributes->setBundleProductOptions($optionList);
                $product->setExtensionAttributes($extensionAttributes);
            }
        }

        return $result;
    }

    /**
     * Retrieve bundle option collection
     *
     * @param \Magento\Catalog\Model\Product[] $products
     * @return array
     */
    private function getOptionsCollectionByStoreId(array $products) : array
    {
        /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
        $optionsCollection = $this->bundleOption->create()->getResourceCollection();
        // All products in collection will have same store id.
        $optionsCollection->joinValues($products[0]->getStoreId());
        foreach ($products as $product) {
            $optionsCollection->setProductIdFilter($product->getEntityId());
        }
        $optionsCollection->setPositionOrder();

        $this->extensionAttributesJoinProcessor->process($optionsCollection);
        if (empty($optionsCollection->getData())) {
            return [];
        }

        $options = [];
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($optionsCollection as $option) {
            /** @var OptionInterface $optionInterface */
            $optionInterface = $this->optionFactory->create();
            foreach ($products as $product) {
                if ((int)$product->getId() !== (int)$option->getParentId()) {
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
                    ->setSku($product->getSku());
                break;
            }

            if ($optionInterface->getOptionId() === null) {
                continue;
            }

            if (!isset($options[$option->getParentId()])) {
                $options[(int)$option->getParentId()] = [];
            }

            $options[(int)$option->getParentId()][] = $optionInterface;
        }

        return $options;
    }

    /**
     * Hydrate links for input options
     *
     * @param array $optionsMap
     * @return array
     */
    private function hydrateLinks(array $optionsMap) : array
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
}
