<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogRule\Model\ResourceModel\Product\ConditionsToCollectionApplier;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider;
use Magento\Framework\Exception\InputException;

/**
 * Add configurable sub products to catalog rule indexer on full reindex
 */
class ConfigurableProductHandler
{
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    private $configurable;

    /**
     * @var \Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider
     */
    private $configurableProductsProvider;

    /**
     * @var ConditionsToCollectionApplier
     */
    private ConditionsToCollectionApplier $conditionsToCollectionApplier;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $productCollectionFactory;

    /**
     * @var array
     */
    private $childrenProducts = [];

    /**
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable
     * @param ConfigurableProductsProvider $configurableProductsProvider
     * @param CollectionFactory $productCollectionFactory
     * @param ConditionsToCollectionApplier $conditionsToCollectionApplier
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable,
        ConfigurableProductsProvider $configurableProductsProvider,
        CollectionFactory $productCollectionFactory,
        ConditionsToCollectionApplier $conditionsToCollectionApplier
    ) {
        $this->configurable = $configurable;
        $this->configurableProductsProvider = $configurableProductsProvider;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->conditionsToCollectionApplier = $conditionsToCollectionApplier;
    }

    /**
     * Match configurable child products if configurable product match the condition
     *
     * @param \Magento\CatalogRule\Model\Rule $rule
     * @param \Closure $proceed
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundGetMatchingProductIds(
        \Magento\CatalogRule\Model\Rule $rule,
        \Closure $proceed
    ) {
        $productsFilter = $rule->getProductsFilter() ? (array) $rule->getProductsFilter() : [];
        if ($productsFilter) {
            $parentProductIds = $this->configurable->getParentIdsByChild($productsFilter);
            $rule->setProductsFilter(array_unique(array_merge($productsFilter, $parentProductIds)));
        }

        $productIds = $proceed();

        $configurableProductIds = $this->configurableProductsProvider->getIds(array_keys($productIds));
        foreach ($configurableProductIds as $productId) {
            if (!isset($this->childrenProducts[$productId])) {
                $this->childrenProducts[$productId] = $this->configurable->getChildrenIds($productId)[0];
            }

            if (isset($this->childrenProducts[$productId])) {
                $this->childrenProducts[$productId] =
                    $this->validateChildrenProducts($rule, $this->childrenProducts[$productId])
                    ?? $this->childrenProducts[$productId];
            }

            $subProductIds = $this->childrenProducts[$productId];
            $parentValidationResult = isset($productIds[$productId])
                ? array_filter($productIds[$productId])
                : [];
            $processAllChildren = !$productsFilter || in_array($productId, $productsFilter);
            foreach ($subProductIds as $subProductId) {
                if ($processAllChildren || in_array($subProductId, $productsFilter)) {
                    $childValidationResult = isset($productIds[$subProductId])
                        ? array_filter($productIds[$subProductId])
                        : [];

                    if (isset($productIds[$subProductId])) {
                        $productIds[$subProductId] = $parentValidationResult + $childValidationResult;
                    }
                }

            }
            unset($productIds[$productId]);
        }
        return $productIds;
    }

    /**
     * @param $rule
     * @param $productIds
     * @return mixed
     */
    private function validateChildrenProducts($rule, $productIds): mixed
    {
        try {
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
            $productCollection = [];

            if ($rule->getConditions()) {
                $productCollection = $this->conditionsToCollectionApplier
                    ->applyConditionsToCollection($rule->getConditions(), $collection);
            }

            return $productCollection->getAllIds() ?? false;
        } catch (InputException $e) {
            return false;
        }
    }
}
