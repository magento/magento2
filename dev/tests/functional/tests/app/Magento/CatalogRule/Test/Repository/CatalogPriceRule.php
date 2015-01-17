<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class CatalogPriceRule Repository
 *
 */
class CatalogPriceRule extends AbstractRepository
{
    const CATALOG_PRICE_RULE = 'catalog_price_rule';

    const CATALOG_PRICE_RULE_ALL_GROUPS = 'catalog_price_rule_all_groups';

    const CUSTOMER_GROUP_GENERAL_RULE = 'customer_group_general_rule';

    const GROUP_RULE_INFORMATION = 'rule_information';

    const GROUP_CONDITIONS = 'conditions';

    const GROUP_ACTIONS = 'actions';

    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = ['config' => $defaultConfig, 'data' => $defaultData];
        $this->_data[self::CATALOG_PRICE_RULE] = $this->_getCatalogPriceRule();
        $this->_data[self::CATALOG_PRICE_RULE_ALL_GROUPS] = array_replace_recursive(
            $this->_getCatalogPriceRule(),
            $this->_getCatalogPriceRuleAllGroups()
        );
    }

    protected function _getCatalogPriceRule()
    {
        return [
            'data' => [
                'fields' => [
                    'name' => ['value' => 'Rule %isolation%', 'group' => static::GROUP_RULE_INFORMATION],
                    'is_active' => [
                        'value' => 'Active',
                        'group' => static::GROUP_RULE_INFORMATION,
                        'input' => 'select',
                    ],
                    'website_ids' => [
                        'value' => ['Main Website'],
                        'group' => static::GROUP_RULE_INFORMATION,
                        'input' => 'multiselect',
                        'input_value' => ['1'],
                    ],
                    'customer_group_ids' => [
                        'value' => ['%group_value%'],
                        'group' => static::GROUP_RULE_INFORMATION,
                        'input' => 'multiselect',
                        'input_value' => ['%group_id%'],
                    ],
                    'simple_action' => [
                        'value' => 'By Percentage of the Original Price',
                        'group' => static::GROUP_ACTIONS,
                        'input' => 'select',
                    ],
                    'discount_amount' => ['value' => '50.0000', 'group' => static::GROUP_ACTIONS],
                    'conditions' => [
                        'value' => '[Category|is|%category_id%]',
                        'group' => static::GROUP_CONDITIONS,
                        'input' => 'conditions',
                        'input_value' => 'Magento\CatalogRule\Model\Rule\Condition\Product|category_ids',
                    ],
                ],
            ]
        ];
    }

    protected function _getCatalogPriceRuleAllGroups()
    {
        return [
            'data' => [
                'fields' => [
                    'customer_group_ids' => [
                        'value' => ['NOT LOGGED IN', 'General', 'Wholesale', 'Retailer'],
                        'group' => static::GROUP_RULE_INFORMATION,
                        'input' => 'multiselect',
                        'input_value' => ['0', '1', '2', '3'],
                    ],
                ],
            ]
        ];
    }
}
