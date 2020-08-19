<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Eav\Model\Attribute\DataProvider;

use Magento\Store\Model\Store;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Base POST data for create attribute.
 */
abstract class AbstractBaseAttributeData
{
    /**
     * Default POST data for create attribute.
     *
     * @var array
     */
    protected $defaultAttributePostData = [
        'active_tab' => 'main',
        'frontend_label' => [
            Store::DEFAULT_STORE_ID => 'Test attribute name',
        ],
        'is_required' => '0',
        'dropdown_attribute_validation' => '',
        'dropdown_attribute_validation_unique' => '',
        'attribute_code' => '',
        'is_global' => ScopedAttributeInterface::SCOPE_STORE,
        'default_value_text' => '',
        'default_value_yesno' => '0',
        'default_value_date' => '',
        'default_value_textarea' => '',
        'is_unique' => '0',
        'is_used_in_grid' => '1',
        'is_visible_in_grid' => '1',
        'is_filterable_in_grid' => '1',
        'is_searchable' => '0',
        'is_comparable' => '0',
        'is_used_for_promo_rules' => '0',
        'is_html_allowed_on_front' => '1',
        'is_visible_on_front' => '0',
        'used_in_product_listing' => '0',
    ];

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->defaultAttributePostData['frontend_input'] = $this->getFrontendInput();
    }

    /**
     * Return create product attribute data set.
     *
     * @return array
     */
    public function getAttributeData(): array
    {
        return [
            "{$this->getFrontendInput()}_with_required_fields" => [
                $this->defaultAttributePostData,
            ],
            "{$this->getFrontendInput()}_with_store_view_scope" => [
                $this->defaultAttributePostData,
            ],
            "{$this->getFrontendInput()}_with_global_scope" => [
                array_merge($this->defaultAttributePostData, ['is_global' => ScopedAttributeInterface::SCOPE_GLOBAL]),
            ],
            "{$this->getFrontendInput()}_with_website_scope" => [
                array_merge($this->defaultAttributePostData, ['is_global' => ScopedAttributeInterface::SCOPE_WEBSITE]),
            ],
            "{$this->getFrontendInput()}_with_attribute_code" => [
                array_merge($this->defaultAttributePostData, ['attribute_code' => 'test_custom_attribute_code']),
            ],
            "{$this->getFrontendInput()}_with_default_value" => [
                array_merge($this->defaultAttributePostData, ['default_value_text' => 'Default attribute value']),
            ],
            "{$this->getFrontendInput()}_without_default_value" => [
                $this->defaultAttributePostData,
            ],
            "{$this->getFrontendInput()}_with_unique_value" => [
                array_merge($this->defaultAttributePostData, ['is_unique' => '1']),
            ],
            "{$this->getFrontendInput()}_without_unique_value" => [
                $this->defaultAttributePostData,
            ],
            "{$this->getFrontendInput()}_with_enabled_add_to_column_options" => [
                array_merge($this->defaultAttributePostData, ['is_used_in_grid' => '1']),
            ],
            "{$this->getFrontendInput()}_without_enabled_add_to_column_options" => [
                array_merge($this->defaultAttributePostData, ['is_used_in_grid' => '0']),
            ],
            "{$this->getFrontendInput()}_with_enabled_use_in_filter_options" => [
                $this->defaultAttributePostData,
            ],
            "{$this->getFrontendInput()}_without_enabled_use_in_filter_options" => [
                array_merge($this->defaultAttributePostData, ['is_filterable_in_grid' => '0']),
            ],
        ];
    }

    /**
     * Return create product attribute data set with error message.
     *
     * @return array
     */
    public function getAttributeDataWithErrorMessage(): array
    {
        $wrongAttributeCode = 'Attribute code "????" is invalid. Please use only letters (a-z or A-Z), numbers ';
        $wrongAttributeCode .= '(0-9) or underscore (_) in this field, and the first character should be a letter.';

        return [
            "{$this->getFrontendInput()}_with_wrong_frontend_input" => [
                array_merge($this->defaultAttributePostData, ['frontend_input' => 'wrong_input_type']),
                (string)__('Input type "wrong_input_type" not found in the input types list.')
            ],
            "{$this->getFrontendInput()}_with_wrong_attribute_code" => [
                array_merge($this->defaultAttributePostData, ['attribute_code' => '????']),
                (string)__($wrongAttributeCode)
            ],
        ];
    }

    /**
     * Return create product attribute data set with array for check data.
     *
     * @return array
     */
    public function getAttributeDataWithCheckArray(): array
    {
        return array_merge_recursive(
            $this->getAttributeData(),
            [
                "{$this->getFrontendInput()}_with_required_fields" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                    ],
                ],
                "{$this->getFrontendInput()}_with_store_view_scope" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'is_global' => ScopedAttributeInterface::SCOPE_STORE,
                    ],
                ],
                "{$this->getFrontendInput()}_with_global_scope" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    ],
                ],
                "{$this->getFrontendInput()}_with_website_scope" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'is_global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                    ],
                ],
                "{$this->getFrontendInput()}_with_attribute_code" => [
                    [
                        'attribute_code' => 'test_custom_attribute_code',
                    ],
                ],
                "{$this->getFrontendInput()}_with_default_value" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'default_value' => 'Default attribute value',
                    ],
                ],
                "{$this->getFrontendInput()}_without_default_value" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'default_value_text' => '',
                    ],
                ],
                "{$this->getFrontendInput()}_with_unique_value" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'is_unique' => '1',
                    ],
                ],
                "{$this->getFrontendInput()}_without_unique_value" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'is_unique' => '0',
                    ],
                ],
                "{$this->getFrontendInput()}_with_enabled_add_to_column_options" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'is_used_in_grid' => '1',
                    ],
                ],
                "{$this->getFrontendInput()}_without_enabled_add_to_column_options" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'is_used_in_grid' => false,
                    ],
                ],
                "{$this->getFrontendInput()}_with_enabled_use_in_filter_options" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'is_filterable_in_grid' => '1',
                    ],
                ],
                "{$this->getFrontendInput()}_without_enabled_use_in_filter_options" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'is_filterable_in_grid' => false,
                    ],
                ],
            ]
        );
    }

    /**
     * Return product attribute data set for update attribute.
     *
     * @return array
     */
    public function getUpdateProvider(): array
    {
        $frontendInput = $this->getFrontendInput();
        return [
            "{$frontendInput}_update_all_fields" => [
                'post_data' => $this->getUpdatePostData(),
                'expected_data' => $this->getUpdateExpectedData(),
            ],
            "{$frontendInput}_other_is_user_defined" => [
                'post_data' => [
                    'is_user_defined' => '2',
                ],
                'expected_data' => [
                    'is_user_defined' => '1',
                ],
            ],
            "{$frontendInput}_with_is_global_null" => [
                'post_data' => [
                    'is_global' => null,
                ],
                'expected_data' => [
                    'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ],
            ],
            "{$frontendInput}_is_visible_in_advanced_search" => [
                'post_data' => [
                    'is_searchable' => '0',
                    'is_visible_in_advanced_search' => '1',
                ],
                'expected_data' => [
                    'is_searchable' => '0',
                    'is_visible_in_advanced_search' => '0',
                ],
            ],
            "{$frontendInput}_update_with_attribute_set" => [
                'post_data' => [
                    'set' => '4',
                    'new_attribute_set_name' => 'Text Attribute Set',
                    'group' => 'text_attribute_group',
                    'groupName' => 'Text Attribute Group',
                    'groupSortOrder' => '1',
                ],
                'expected_data' => [],
            ],
        ];
    }

    /**
     * Return product attribute data set with error message for update attribute.
     *
     * @return array
     */
    public function getUpdateProviderWithErrorMessage(): array
    {
        $frontendInput = $this->getFrontendInput();
        return [
            "{$frontendInput}_same_attribute_set_name" => [
                'post_data' => [
                    'set' => '4',
                    'new_attribute_set_name' => 'Default',
                ],
                'error_message' => (string)__('An attribute set named \'Default\' already exists.'),
            ],
            "{$frontendInput}_empty_set_id" => [
                'post_data' => [
                    'set' => '',
                    'new_attribute_set_name' => 'Text Attribute Set',
                ],
                'error_message' => (string)__('Something went wrong while saving the attribute.'),
            ],
            "{$frontendInput}_nonexistent_attribute_id" => [
                'post_data' => [
                    'attribute_id' => 9999,
                ],
                'error_message' => (string)__('This attribute no longer exists.'),
            ],
            "{$frontendInput}_attribute_other_entity_type" => [
                'post_data' => [
                    'attribute_id' => 45,
                ],
                'error_message' => (string)__('We can\'t update the attribute.'),
            ],
        ];
    }

    /**
     * Return product attribute data set for update attribute frontend labels.
     *
     * @return array
     */
    public function getUpdateFrontendLabelsProvider(): array
    {
        $frontendInput = $this->getFrontendInput();
        return [
            "{$frontendInput}_update_frontend_label" => [
                'post_data' => [
                    'frontend_label' => [
                        Store::DEFAULT_STORE_ID => 'Test Attribute Update',
                        'default' => 'Default Store Update',
                        'fixture_second_store' => 'Second Store Update',
                        'fixture_third_store' => 'Third Store Update',
                    ]
                ],
                'expected_data' => [
                    'frontend_label' => 'Test Attribute Update',
                    'store_labels' => [
                        'default' => 'Default Store Update',
                        'fixture_second_store' => 'Second Store Update',
                        'fixture_third_store' => 'Third Store Update',
                    ],
                ],
            ],
            "{$frontendInput}_remove_frontend_label" => [
                'post_data' => [
                    'frontend_label' => [
                        Store::DEFAULT_STORE_ID => 'Test Attribute Update',
                        'default' => 'Default Store Update',
                        'fixture_second_store' => '',
                        'fixture_third_store' => '',
                    ]
                ],
                'expected_data' => [
                    'frontend_label' => 'Test Attribute Update',
                    'store_labels' => [
                        'default' => 'Default Store Update',
                    ],
                ],
            ],
            "{$frontendInput}_with_frontend_label_string" => [
                'post_data' => [
                    'frontend_label' => 'Test Attribute Update',
                ],
                'expected_data' => [
                    'frontend_label' => 'Test Attribute Update',
                    'store_labels' => [
                        'default' => 'Default Store View',
                        'fixture_second_store' => 'Fixture Second Store',
                        'fixture_third_store' => 'Fixture Third Store',
                    ],
                ],
            ],
        ];
    }

    /**
     * Return attribute frontend input.
     *
     * @return string
     */
    abstract protected function getFrontendInput(): string;

    /**
     * Return post data for attribute update.
     *
     * @return array
     */
    abstract protected function getUpdatePostData(): array;

    /**
     * Return expected data for attribute update.
     *
     * @return array
     */
    abstract protected function getUpdateExpectedData(): array;
}
