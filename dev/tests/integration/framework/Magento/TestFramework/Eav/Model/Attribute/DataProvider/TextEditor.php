<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Eav\Model\Attribute\DataProvider;

/**
 * Product attribute data for attribute with text editor input type.
 */
class TextEditor extends AbstractBaseAttributeData
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaultAttributePostData['used_for_sort_by'] = '0';
    }

    /**
     * @inheritdoc
     */
    public function getAttributeData(): array
    {
        return array_replace_recursive(
            parent::getAttributeData(),
            [
                "{$this->getFrontendInput()}_with_default_value" => [
                    [
                        'default_value_text' => '',
                        'default_value_textarea' => 'Default attribute value',
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getAttributeDataWithCheckArray(): array
    {
        return array_replace_recursive(
            parent::getAttributeDataWithCheckArray(),
            [
                "{$this->getFrontendInput()}_with_required_fields" => [
                    1 => [
                        'frontend_input' => 'textarea',
                    ],
                ],
                "{$this->getFrontendInput()}_with_store_view_scope" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{$this->getFrontendInput()}_with_global_scope" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{$this->getFrontendInput()}_with_website_scope" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{$this->getFrontendInput()}_with_attribute_code" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{$this->getFrontendInput()}_with_default_value" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{$this->getFrontendInput()}_without_default_value" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{$this->getFrontendInput()}_with_unique_value" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{$this->getFrontendInput()}_without_unique_value" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{$this->getFrontendInput()}_with_enabled_add_to_column_options" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{$this->getFrontendInput()}_without_enabled_add_to_column_options" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{$this->getFrontendInput()}_with_enabled_use_in_filter_options" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
                "{$this->getFrontendInput()}_without_enabled_use_in_filter_options" => [
                    1 => [
                        'frontend_input' => 'textarea'
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function getFrontendInput(): string
    {
        return 'texteditor';
    }
}
