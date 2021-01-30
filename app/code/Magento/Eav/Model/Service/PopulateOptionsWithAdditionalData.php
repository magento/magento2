<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Service;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Add additional data to options service class
 */
class PopulateOptionsWithAdditionalData
{
    /**
     * @var CollectionFactory
     */
    private $optionCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AttributeOptionLabelInterfaceFactory
     */
    private $labelFactory;

    /**
     * @param CollectionFactory $optionCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param AttributeOptionLabelInterfaceFactory $labelFactory
     */
    public function __construct(
        CollectionFactory $optionCollectionFactory,
        StoreManagerInterface $storeManager,
        AttributeOptionLabelInterfaceFactory $labelFactory
    ) {
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->storeManager = $storeManager;
        $this->labelFactory = $labelFactory;
    }

    /**
     * Add additional data to attribute options
     *
     * @param AttributeInterface $attribute
     * @return AttributeOptionInterface[]|null
     */
    public function execute(AttributeInterface $attribute): ?array
    {
        if (!$attribute->usesSource()) {
            return $attribute->getOptions();
        }
        $options = $this->prepareOriginalOptions($attribute);
        if (!empty($options) && $attribute->getSource() instanceof Table) {
            $optionItems = $this->getOptionItems($attribute);
            $this->fillOptionsData($options, $optionItems);
        }

        return $options;
    }

    /**
     * Add to options array value keys and default value
     *
     * @param AttributeInterface $attribute
     * @return array
     */
    private function prepareOriginalOptions(AttributeInterface $attribute): array
    {
        return array_reduce(
            $attribute->getOptions(),
            function (array $result, AttributeOptionInterface $option) use ($attribute) {
                $option->setIsDefault($attribute->getDefaultValue() === (string)$option->getValue());
                $result[$option->getValue()] = $option;

                return $result;
            },
            []
        );
    }

    /**
     * Load additional options data
     *
     * @param AttributeInterface $attribute
     * @return array
     */
    private function getOptionItems(AttributeInterface $attribute): array
    {
        $collection = $this->optionCollectionFactory->create();
        $collection->setAttributeFilter($attribute->getAttributeId());
        /** @var StoreManagerInterface $storeManager */
        foreach ($this->storeManager->getStores() as $store) {
            $storeCode = $store->getCode();
            $alias = 'store_' . $storeCode;

            $collection->getSelect()->joinLeft(
                [$alias => 'eav_attribute_option_value'],
                "main_table.option_id = $alias.option_id and $alias.store_id = {$store->getId()}",
                [$storeCode . '_store_id' => "$alias.store_id", $storeCode . '_value' => "$alias.value"]
            );
        }

        return $collection->getItems();
    }

    /**
     * Adds additional options data to original options array
     *
     * @param array $optionItems
     * @param array $options
     */
    private function fillOptionsData(array $options, array $optionItems): void
    {
        foreach ($optionItems as $item) {
            $labels = [];
            foreach ($this->storeManager->getStores() as $store) {
                $storeCode = $store->getCode();
                if (!empty($item->getData($storeCode . '_store_id'))) {
                    $labels[] = $this->labelFactory->create()
                        ->setData([
                            'store_id' => $item->getData($storeCode . '_store_id'),
                            'label' => $item->getData($storeCode . '_value'),
                        ]);
                }
            }
            $key = (string)$item->getId();
            $options[$key]->setSortOrder($item->getSortOrder());
            $options[$key]->setStoreLabels($labels ?: null);
        }
    }
}
