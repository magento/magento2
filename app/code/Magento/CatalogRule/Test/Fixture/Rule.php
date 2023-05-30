<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Fixture;

use Magento\CatalogRule\Test\Fixture\Data\ActionsSerializer;
use Magento\CatalogRule\Test\Fixture\Data\ConditionsSerializer;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\CatalogRule\Model\ResourceModel\Rule as ResourceModel;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class Rule implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'name' => 'catalogrule%uniqid%',
        'sort_order' => 0,
        'is_active' => 1,
        'description' => null,
        'website_ids' => [],
        'customer_group_ids' => [],
        'stop_rules_processing' => true,
        'simple_action' => 'by_percent',
        'discount_amount' => 0,
        'conditions' => [],
        'actions' => [],
    ];

    /**
     * @var ProcessorInterface
     */
    private ProcessorInterface $dataProcessor;

    /**
     * @var ResourceModel
     */
    private ResourceModel $catalogRuleResourceModel;

    /**
     * @var RuleFactory
     */
    private RuleFactory $catalogRuleFactory;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $customerGroupCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ConditionsSerializer
     */
    private ConditionsSerializer $conditionsSerializer;

    /**
     * @var ActionsSerializer
     */
    private ActionsSerializer $actionsSerializer;

    /**
     * @param ProcessorInterface $dataProcessor
     * @param ResourceModel $catalogRuleResourceModel
     * @param RuleFactory $catalogRuleFactory
     * @param CollectionFactory $customerGroupCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ConditionsSerializer $conditionsSerializer
     * @param ActionsSerializer $actionsSerializer
     */
    public function __construct(
        ProcessorInterface $dataProcessor,
        ResourceModel $catalogRuleResourceModel,
        RuleFactory $catalogRuleFactory,
        CollectionFactory $customerGroupCollectionFactory,
        StoreManagerInterface $storeManager,
        ConditionsSerializer $conditionsSerializer,
        ActionsSerializer $actionsSerializer
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->catalogRuleResourceModel = $catalogRuleResourceModel;
        $this->catalogRuleFactory = $catalogRuleFactory;
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
        $this->storeManager = $storeManager;
        $this->conditionsSerializer = $conditionsSerializer;
        $this->actionsSerializer = $actionsSerializer;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        /** @var \Magento\CatalogRule\Model\Rule $model */
        $model = $this->catalogRuleFactory->create();
        $data = $this->prepareData($data);
        $model->setData($data);
        $this->catalogRuleResourceModel->save($model);

        return $model;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as Rule::DEFAULT_DATA.
     * - $data['conditions']: can be supplied in following formats:
     *      - [['attribute'=>'..','value'=>'..'],['attribute'=>'..','value'=>'..','operator'=>'..'], [..]]
     *      - ['aggregator'=>'any', 'conditions' => [[..],[..]]]
     */
    public function revert(DataObject $data): void
    {
        /** @var \Magento\CatalogRule\Model\Rule $model */
        $model = $this->catalogRuleFactory->create();
        $this->catalogRuleResourceModel->load($model, $data->getId());
        if ($model->getId()) {
            $this->catalogRuleResourceModel->delete($model);
        }
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

    /**
     * Prepare CatalogRule data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = array_merge($this->prepareDefaultData(), $data);
        $data['conditions_serialized'] = $this->conditionsSerializer->serialize($data['conditions']);
        $data['actions_serialized'] = $this->actionsSerializer->serialize($data['actions']);
        unset($data['conditions'], $data['actions']);

        return $this->dataProcessor->process($this, $data);
    }
}
