<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\SalesRule\Model\RegistryConstants;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Metadata\ValueProvider as MetadataValueProvider;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Initialize dependencies.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Registry $registry
     * @param Metadata\ValueProvider $metadataValueProvider
     * @param array $meta
     * @param array $data
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Registry $registry,
        protected readonly MetadataValueProvider $metadataValueProvider,
        array $meta = [],
        array $data = [],
        private ?DataPersistorInterface $dataPersistor = null
    ) {
        $this->collection = $collectionFactory->create();
        $this->coreRegistry = $registry;
        $meta = array_replace_recursive($this->getMetadataValues(), $meta);
        $this->dataPersistor = $dataPersistor ?? ObjectManager::getInstance()->get(
            DataPersistorInterface::class
        );
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get metadata values
     *
     * @return array
     */
    protected function getMetadataValues()
    {
        $rule = $this->coreRegistry->registry(RegistryConstants::CURRENT_SALES_RULE);
        return $this->metadataValueProvider->getMetadataValues($rule);
    }

    /**
     * @inheritdoc
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
        $data = $this->dataPersistor->get('sale_rule');
        if (!empty($data)) {
            $rule = $this->collection->getNewEmptyItem();
            $rule->setData($data);
            $this->loadedData[$rule->getId()] = $rule->getData();
            $this->dataPersistor->clear('sale_rule');
        }

        return $this->loadedData;
    }
}
