<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Fixture;

class CustomerAttributeDefaultData
{
    private const DEFAULT_DATA = [
        'entity_type_id' => null,
        'attribute_id' => null,
        'attribute_code' => 'attribute%uniqid%',
        'default_frontend_label' => 'Attribute%uniqid%',
        'frontend_labels' => [],
        'frontend_input' => 'text',
        'frontend_label' => null,
        'backend_type' => 'varchar',
        'is_required' => false,
        'is_user_defined' => true,
        'note' => null,
        'backend_model' => null,
        'source_model' => null,
        'default_value' => null,
        'is_unique' => '0',
        'frontend_class' => null,
        'used_in_forms' => [],
        'sort_order' => 0,
        'attribute_set_id' => null,
        'attribute_group_id' => null,
        'input_filter' => null,
        'multiline_count' => 0,
        'validate_rules' => null,
        'website_id' => null,
        'is_visible' => 1,
        'scope_is_visible' => 1,
    ];

    /**
     * @var array
     */
    private $defaultData;

    /**
     * @param array $defaultData
     */
    public function __construct(array $defaultData = [])
    {
        $this->defaultData = array_merge(self::DEFAULT_DATA, $defaultData);
    }

    /**
     * Return default data
     */
    public function getData(): array
    {
        return $this->defaultData;
    }
}
