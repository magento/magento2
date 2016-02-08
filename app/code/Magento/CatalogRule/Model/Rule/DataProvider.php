<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Rule;

use Magento\CatalogRule\Model\ResourceModel\Rule\Collection;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\CatalogRule\Model\Rule;
use Magento\Store\Model\System\Store;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;
use Magento\CatalogRule\Model\Rule\Action\SimpleActionOptionsProvider;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
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
     * @var SimpleActionOptionsProvider
     */
    protected $actionOptions;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Store $store
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DataObject $objectConverter
     * @param SimpleActionOptionsProvider $actionOptions
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
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
        SimpleActionOptionsProvider $actionOptions,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->groupRepository = $groupRepository;
        $this->collection = $collectionFactory->create();
        $this->store = $store;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
        $this->actionOptions = $actionOptions;
        $this->dataPersistor = $dataPersistor;

        $meta = array_replace_recursive($meta, $this->getMetadata());
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    protected function getMetadata()
    {
        $customerGroups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        return [
            'rule_information' => [
                'children' => [
                    'website_ids' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => $this->store->getWebsiteValuesForForm(),
                                ],
                            ],
                        ],
                    ],
                    'is_active' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => [
                                        ['label' => __('Active'), 'value' => '1'],
                                        ['label' => __('Inactive'), 'value' => '0']
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'customer_group_ids' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => $this->objectConverter->toOptionArray($customerGroups, 'id', 'code'),
                                ],
                            ],
                        ],
                    ]
                ]
            ],
            'actions' => [
                'children' => [
                    'simple_action' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => $this->actionOptions->toOptionArray()
                                ],
                            ],
                        ],
                    ],
                    'stop_rules_processing' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => [
                                        ['label' => __('Yes'), 'value' => '1'],
                                        ['label' => __('No'), 'value' => '0']
                                    ]
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        ];
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
            $this->loadedData[$rule->getId()] = $rule->getData();
        }

        $data = $this->dataPersistor->get('catalog_rule');
        if (!empty($data)) {
            $rule = $this->collection->getNewEmptyItem();
            $rule->setData($data);
            $this->loadedData[$rule->getId()] = $rule->getData();
        }

        return $this->loadedData;
    }
}
