<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\BundleGraphQl\Model\Resolver\Options;

use Magento\Bundle\Model\OptionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Collection to fetch bundle option data at resolution time.
 */
class Collection
{
    /**
     * @var OptionFactory
     */
    private $bundleOptionFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string[]
     */
    private $skuMap = [];

    /**
     * @var array
     */
    private $optionMap = [];

    /**
     * @param OptionFactory $bundleOptionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        OptionFactory $bundleOptionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->bundleOptionFactory = $bundleOptionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Add parent id/sku pair to use for option filter at fetch time.
     *
     * @param int $parentId
     * @param int $parentEntityId
     * @param string $sku
     */
    public function addParentFilterData(int $parentId, int $parentEntityId, string $sku) : void
    {
        $this->skuMap[$parentId] = ['sku' => $sku, 'entity_id' => $parentEntityId];
    }

    /**
     * Fetch data for bundle options and return the options for the given parent id.
     *
     * @param int $parentId
     * @return array
     */
    public function getOptionsByParentId(int $parentId) : array
    {
        $options = $this->fetch();
        if (!isset($options[$parentId])) {
            return [];
        }

        return $options[$parentId];
    }

    /**
     * Fetch bundle option data and return in array format. Keys for bundle options will be their parent product ids.
     *
     * @return array
     */
    private function fetch() : array
    {
        if (empty($this->skuMap) || !empty($this->optionMap)) {
            return $this->optionMap;
        }

        /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
        $optionsCollection = $this->bundleOptionFactory->create()->getResourceCollection();
        // All products in collection will have same store id.
        $optionsCollection->joinValues($this->storeManager->getStore()->getId());

        $productTable = $optionsCollection->getTable('catalog_product_entity');
        $linkField = $optionsCollection->getConnection()->getAutoIncrementField($productTable);
        $optionsCollection->getSelect()->join(
            ['cpe' => $productTable],
            'cpe.'.$linkField.' = main_table.parent_id',
            []
        )->where(
            "cpe.entity_id IN (?)",
            $this->skuMap
        );
        $optionsCollection->setPositionOrder();

        $this->extensionAttributesJoinProcessor->process($optionsCollection);
        if (empty($optionsCollection->getData())) {
            return null;
        }

        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($optionsCollection as $option) {
            if (!isset($this->optionMap[$option->getParentId()])) {
                $this->optionMap[$option->getParentId()] = [];
            }
            $this->optionMap[$option->getParentId()][$option->getId()] = $option->getData();
            $this->optionMap[$option->getParentId()][$option->getId()]['title']
                = $option->getTitle() === null ? $option->getDefaultTitle() : $option->getTitle();
            $this->optionMap[$option->getParentId()][$option->getId()]['sku']
                = $this->skuMap[$option->getParentId()]['sku'];
        }

        return $this->optionMap;
    }
}
