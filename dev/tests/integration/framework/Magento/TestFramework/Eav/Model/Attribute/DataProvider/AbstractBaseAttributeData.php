<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Eav\Model\Attribute\DataProvider;

use Magento\Store\Model\Store;

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
        'is_global' => '0',
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
                array_merge($this->defaultAttributePostData, ['is_global' => '1']),
            ],
            "{$this->getFrontendInput()}_with_website_scope" => [
                array_merge($this->defaultAttributePostData, ['is_global' => '2']),
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
                        'is_global' => '0',
                    ],
                ],
                "{$this->getFrontendInput()}_with_global_scope" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'is_global' => '1',
                    ],
                ],
                "{$this->getFrontendInput()}_with_website_scope" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'is_global' => '2',
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
     * Return attribute frontend input.
     *
     * @return string
     */
    abstract protected function getFrontendInput(): string;
}
