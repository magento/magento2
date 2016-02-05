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
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->store = $store;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
        $this->salesRuleFactory = $salesRuleFactory;
        $this->coreRegistry = $registry;
        $this->initMeta();
    }

    /**
     * @return void
     */
    protected function initMeta()
    {
        $customerGroups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        $applyOptions = [
            ['label' => __('Percent of product price discount'), 'value' =>  Rule::BY_PERCENT_ACTION],
            ['label' => __('Fixed amount discount'), 'value' => Rule::BY_FIXED_ACTION],
            ['label' => __('Fixed amount discount for whole cart'), 'value' => Rule::BY_PERCENT_ACTION],
            ['label' => __('Buy X get Y free (discount amount is Y)'), 'value' => Rule::BUY_X_GET_Y_ACTION]
        ];

        $couponTypesOptions = [];
        $couponTypes = $this->salesRuleFactory->create()->getCouponTypes();
        foreach ($couponTypes as $key => $couponType) {
            $couponTypesOptions[] = [
                'label' => $couponType,
                'value' => $key,
            ];
        }

        $rule = $rule = $this->coreRegistry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);
        $labels = $rule->getStoreLabels();

        $this->meta = [
            'rule_information' => [
                'fields' => [
                    'website_ids' => [
                        'options' => $this->store->getWebsiteValuesForForm()
                    ],
                    'is_active' => [
                        'options' => [
                            ['label' => __('Active'), 'value' => '1'],
                            ['label' => __('Inactive'), 'value' => '0']
                        ]
                    ],
                    'customer_group_ids' => [
                        'options' => $this->objectConverter->toOptionArray($customerGroups, 'id', 'code')
                    ],
                    'coupon_type' => [
                        'options' => $couponTypesOptions
                    ],
                    'is_rss' => [
                        'options' => [
                            ['label' => __('Yes'), 'value' => '1'],
                            ['label' => __('No'), 'value' => '0']
                        ]
                    ],
                ]
            ],
            'actions' => [
                'fields' => [
                    'simple_action' => [
                        'options' => $applyOptions
                    ],
                    'discount_amount' => [
                        'value' => '0',
                    ],
                    'discount_qty' => [
                        'value' => '0',
                    ],
                    'apply_to_shipping' => [
                        'options' => [
                            ['label' => __('Yes'), 'value' => '1'],
                            ['label' => __('No'), 'value' => '0']
                        ]
                    ],
                    'stop_rules_processing' => [
                        'options' => [
                            ['label' => __('Yes'), 'value' => '1'],
                            ['label' => __('No'), 'value' => '0'],
                        ]
                    ],
                ]
            ],
            'labels' => [
                'fields' => [
                    'store_labels[0]' => [
                        'value' => isset($labels[0]) ? $labels[0] : '',
                    ]
                ]
            ],
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
            $rule->setDiscountAmount($rule->getDiscountAmount() * 1);
            $rule->setDiscountQty($rule->getDiscountQty() * 1);

            $this->loadedData[$rule->getId()] = $rule->getData();
        }

        return $this->loadedData;
    }
}
