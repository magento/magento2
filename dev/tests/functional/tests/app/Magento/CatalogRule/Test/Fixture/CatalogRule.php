<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class CatalogRule
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CatalogRule extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\CatalogRule\Test\Repository\CatalogRule';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\CatalogRule\Test\Handler\CatalogRule\CatalogRuleInterface';

    protected $defaultDataSet = [
        'name' => 'CatalogPriceRule %isolation%',
        'description' => 'Catalog Price Rule Description',
        'is_active' => 'Active',
        'website_ids' => ['Main Website'],
        'customer_group_ids' => ['NOT LOGGED IN'],
        'simple_action' => 'By Percentage of the Original Price',
        'discount_amount' => '50',
    ];

    protected $name = [
        'attribute_code' => 'name',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'text',
        'group' => 'rule_information',
    ];

    protected $description = [
        'attribute_code' => 'description',
        'default_value' => '',
        'input' => 'text',
        'group' => 'rule_information',
    ];

    protected $is_active = [
        'attribute_code' => 'is_active',
        'backend_type' => 'smallint',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'select',
        'group' => 'rule_information',
    ];

    protected $website_ids = [
        'attribute_code' => 'website_ids',
        'backend_type' => 'smallint',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'multiselect',
        'group' => 'rule_information',
    ];

    protected $customer_group_ids = [
        'attribute_code' => 'customer_group_ids',
        'backend_type' => 'smallint',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'multiselect',
        'group' => 'rule_information',
    ];

    protected $from_date = [
        'attribute_code' => 'from_date',
        'backend_type' => 'date',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
        'group' => 'rule_information',
    ];

    protected $to_date = [
        'attribute_code' => 'to_date',
        'backend_type' => 'date',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
        'group' => 'rule_information',
    ];

    protected $simple_action = [
        'attribute_code' => 'simple_action',
        'backend_type' => 'smallint',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'select',
        'group' => 'actions',
    ];

    protected $discount_amount = [
        'attribute_code' => 'discount_amount',
        'backend_type' => 'decimal',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'text',
        'group' => 'actions',
    ];

    protected $condition_type = [
        'attribute_code' => 'condition_type',
        'backend_type' => 'virtual',
        'is_required' => '0',
        'group' => 'conditions',
        'input' => 'select',
    ];

    protected $condition_value = [
        'attribute_code' => 'condition_value',
        'backend_type' => 'virtual',
        'is_required' => '0',
        'group' => 'conditions',
    ];

    protected $rule = [
        'attribute_code' => 'rule',
        'backend_type' => 'virtual',
        'is_required' => '0',
        'group' => 'conditions',
    ];

    protected $conditions = [
        'attribute_code' => 'conditions',
        'backend_type' => 'virtual',
        'group' => 'conditions',
    ];

    protected $id = [
        'attribute_code' => 'id',
        'backend_type' => 'virtual',
    ];

    protected $sort_order = [
        'attribute_code' => 'sort_order',
        'default_value' => '',
        'input' => 'text',
        'group' => 'rule_information',
    ];

    protected $stop_rules_processing = [
        'attribute_code' => 'stop_rules_processing',
        'default_value' => '',
        'input' => 'select',
        'group' => 'rule_information',
    ];

    public function getName()
    {
        return $this->getData('name');
    }

    public function getDescription()
    {
        return $this->getData('description');
    }

    public function getIsActive()
    {
        return $this->getData('is_active');
    }

    public function getWebsiteIds()
    {
        return $this->getData('website_ids');
    }

    public function getCustomerGroupIds()
    {
        return $this->getData('customer_group_ids');
    }

    public function getFromDate()
    {
        return $this->getData('from_date');
    }

    public function getToDate()
    {
        return $this->getData('to_date');
    }

    public function getSimpleAction()
    {
        return $this->getData('simple_action');
    }

    public function getDiscountAmount()
    {
        return $this->getData('discount_amount');
    }

    public function getConditionType()
    {
        return $this->getData('condition_type');
    }

    public function getConditionValue()
    {
        return $this->getData('condition_value');
    }

    public function getRule()
    {
        return $this->getData('rule');
    }

    public function getConditions()
    {
        return $this->getData('conditions');
    }

    public function getId()
    {
        return $this->getData('id');
    }

    public function getSortOrder()
    {
        return $this->getData('sort_order');
    }

    public function getStopRulesProcessing()
    {
        return $this->getData('stop_rules_processing');
    }
}
