<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class CatalogRule
 * Data for creation Catalog Price Rule
 */
class CatalogRule extends AbstractRepository
{
    /**
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['active_catalog_rule'] = [
            'name' => 'Active Catalog Rule',
            'description' => 'Rule Description',
            'is_active' => 'Active',
            'website_ids' => ['Main Website'],
            'customer_group_ids' => ['NOT LOGGED IN', 'General', 'Wholesale', 'Retailer'],
            'from_date' => '3/25/14',
            'to_date' => '3/29/14',
            'sort_order' => '1',
            'simple_action' => 'By Percentage of the Original Price',
            'discount_amount' => '50',
        ];

        $this->_data['inactive_catalog_price_rule'] = [
            'name' => 'Inactive Catalog Price Rule',
            'is_active' => 'Inactive',
            'website_ids' => ['Main Website'],
            'customer_group_ids' => ['NOT LOGGED IN'],
            'simple_action' => 'By Percentage of the Original Price',
            'discount_amount' => '50',
        ];

        $this->_data['active_catalog_price_rule_with_conditions'] = [
            'name' => 'Active Catalog Rule with conditions %isolation%',
            'description' => 'Rule Description',
            'is_active' => 'Active',
            'website_ids' => ['Main Website'],
            'customer_group_ids' => ['NOT LOGGED IN', 'General', 'Wholesale', 'Retailer'],
            'rule' => '[Category|is|2]',
            'simple_action' => 'By Percentage of the Original Price',
            'discount_amount' => '10',
        ];

        $this->_data['catalog_price_rule_priority_0'] = [
            'name' => 'catalog_price_rule_priority_0',
            'description' => '-50% of price, Priority = 0',
            'is_active' => 'Active',
            'website_ids' => ['Main Website'],
            'customer_group_ids' => ['NOT LOGGED IN'],
            'sort_order' => '0',
            'simple_action' => 'By Percentage of the Original Price',
            'discount_amount' => '50',
        ];

        $this->_data['catalog_price_rule_priority_1_stop_further_rules'] = [
            'name' => 'catalog_price_rule_priority_1_stop_further_rules',
            'description' => 'Priority 1, -5 By fixed amount',
            'is_active' => 'Active',
            'website_ids' => ['Main Website'],
            'customer_group_ids' => ['NOT LOGGED IN'],
            'sort_order' => '1',
            'simple_action' => 'By Fixed Amount',
            'discount_amount' => '5',
            'stop_rules_processing' => 'Yes',
        ];

        $this->_data['catalog_price_rule_priority_2'] = [
            'name' => 'catalog_price_rule_priority_2',
            'description' => 'Priority 2, -10 By fixed amount',
            'is_active' => 'Active',
            'website_ids' => ['Main Website'],
            'customer_group_ids' => ['NOT LOGGED IN'],
            'sort_order' => '2',
            'simple_action' => 'By Fixed Amount',
            'discount_amount' => '10',
        ];
    }
}
