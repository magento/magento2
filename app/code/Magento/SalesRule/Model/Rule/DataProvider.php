<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule;

use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\System\Store;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;

/**
 * Class DataProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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
     * @var Store
     */
    protected $store;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var DataObject
     */
    protected $objectConverter;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $salesRuleFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\SalesRule\Model\Rule\Metadata\ValueProvider
     */
    protected $metadataValueProvider;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Store $store
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DataObject $objectConverter
     * @param \Magento\SalesRule\Model\RuleFactory $salesRuleFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $meta
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Store $store,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataObject $objectConverter,
        \Magento\SalesRule\Model\RuleFactory $salesRuleFactory,
        \Magento\Framework\Registry $registry,
        \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $metadataValueProvider,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->store = $store;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
        $this->salesRuleFactory = $salesRuleFactory;
        $this->coreRegistry = $registry;
        $this->metadataValueProvider = $metadataValueProvider;
        $meta = array_replace_recursive($this->getMetadataValues(), $meta);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get metadata values
     *
     * @return array
     */
    protected function getMetadataValues()
    {
        $rule = $this->coreRegistry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);
        return $this->metadataValueProvider->getMetadataValues($rule);
    }

    /**
     * @return array
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
