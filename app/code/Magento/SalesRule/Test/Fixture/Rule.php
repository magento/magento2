<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Fixture;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\SalesRule\Model\ResourceModel\Rule as ResourceModel;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class Rule implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'name' => 'rule%uniqid%',
        'sort_order' => 0,
        'is_active' => 1,
        'store_labels' => [],
        'description' => null,
        'website_ids' => [],
        'customer_group_ids' => [],
        'from_date' => null,
        'to_date' => null,
        'uses_per_customer' => null,
        'stop_rules_processing' => true,
        'is_advanced' => true,
        'simple_action' => \Magento\SalesRule\Model\Rule::TO_PERCENT_ACTION,
        'discount_amount' => 0,
        'discount_qty' => null,
        'discount_step' => 0,
        'apply_to_shipping' => 0,
        'times_used' => 0,
        'is_rss' => true,
        'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON,
        'use_auto_generation' => 0,
        'uses_per_coupon' => 0,
        'simple_free_shipping' => 0,
        'extension_attributes' => 0,
        'conditions' => [],
        'actions' => [],
    ];

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var ResourceModel
     */
    private $resourceModel;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var CollectionFactory
     */
    private $customerGroupCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ProcessorInterface $dataProcessor
     * @param ResourceModel $resourceModel
     * @param RuleFactory $ruleFactory
     * @param Json $serializer
     * @param CollectionFactory $customerGroupCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProcessorInterface $dataProcessor,
        ResourceModel $resourceModel,
        RuleFactory $ruleFactory,
        Json $serializer,
        CollectionFactory $customerGroupCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->resourceModel = $resourceModel;
        $this->ruleFactory = $ruleFactory;
        $this->serializer = $serializer;
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        /** @var \Magento\SalesRule\Model\Rule $model */
        $model = $this->ruleFactory->create();
        $data = $this->prepareData($data);
        $conditions = $data['conditions'];
        $actions = $data['actions'];
        unset($data['conditions'], $data['actions']);
        $model->setData($this->prepareData($data));

        $model->setActionsSerialized($this->serializer->serialize($actions));
        $model->setConditionsSerialized($this->serializer->serialize($conditions));
        $this->resourceModel->save($model);

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        /** @var \Magento\SalesRule\Model\Rule $model */
        $model = $this->ruleFactory->create();
        $this->resourceModel->load($model, $data->getId());
        if ($model->getId()) {
            $this->resourceModel->delete($model);
        }
    }

    /**
     * Prepare salesrule data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = array_merge($this->prepareDefaultData(), $data);
        $data['conditions'] = $data['conditions'] ?? [];
        $data['actions'] = $data['actions'] ?? [];

        if ($data['conditions'] instanceof DataObject) {
            $data['conditions'] = $data['conditions']->toArray();
        } else {
            $conditions = $data['conditions'];
            $data['conditions'] = Conditions::DEFAULT_DATA;
            foreach ($conditions as $condition) {
                $data['conditions']['conditions'][] = $condition instanceof DataObject
                    ? $condition->toArray()
                    : $condition;
            }
        }

        if ($data['actions'] instanceof DataObject) {
            $data['actions'] = $data['actions']->toArray();
        } else {
            $conditions = $data['actions'];
            $data['actions'] = ProductConditions::DEFAULT_DATA;
            foreach ($conditions as $condition) {
                $data['actions']['conditions'][] = $condition instanceof DataObject
                    ? $condition->toArray()
                    : $condition;
            }
        }

        if (!empty($data['coupon_code'])) {
            $data['coupon_type'] = \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC;
        }

        return $this->dataProcessor->process($this, $data);
    }

    /**
     * Prepares rule default data
     *
     * @return array
     */
    private function prepareDefaultData(): array
    {
        $data = self::DEFAULT_DATA;
        $customerGroupCollection = $this->customerGroupCollectionFactory->create();
        foreach ($customerGroupCollection->getAllIds() as $customerGroupId) {
            $data['customer_group_ids'][] = $customerGroupId;
        }

        foreach ($this->storeManager->getWebsites() as $website) {
            $data['website_ids'][] = $website->getId();
        }

        return $data;
    }
}
