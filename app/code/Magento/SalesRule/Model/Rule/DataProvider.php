<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule;

use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;

/**
 * Class DataProvider
 * @since 2.1.0
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     * @since 2.1.0
     */
    protected $collection;

    /**
     * @var array
     * @since 2.1.0
     */
    protected $loadedData;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.1.0
     */
    protected $coreRegistry;

    /**
     * @var \Magento\SalesRule\Model\Rule\Metadata\ValueProvider
     * @since 2.1.0
     */
    protected $metadataValueProvider;

    /**
     * Initialize dependencies.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param Metadata\ValueProvider $metadataValueProvider
     * @param array $meta
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $metadataValueProvider,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->coreRegistry = $registry;
        $this->metadataValueProvider = $metadataValueProvider;
        $meta = array_replace_recursive($this->getMetadataValues(), $meta);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get metadata values
     *
     * @return array
     * @since 2.1.0
     */
    protected function getMetadataValues()
    {
        $rule = $this->coreRegistry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);
        return $this->metadataValueProvider->getMetadataValues($rule);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Rule $rule */
        foreach ($items as $rule) {
            $rule->load($rule->getId());
            $rule->setDiscountAmount($rule->getDiscountAmount() * 1);
            $rule->setDiscountQty($rule->getDiscountQty() * 1);

            $this->loadedData[$rule->getId()] = $rule->getData();
        }

        return $this->loadedData;
    }
}
