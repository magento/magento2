<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Swatches\Model\Attribute\DataProvider;

use Magento\Swatches\Model\Swatch;

/**
 * Product attribute data for attribute with input type visual swatch.
 */
class TextSwatch extends AbstractSwatchAttributeData
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaultAttributePostData['swatch_input_type'] = 'text';
    }

    /**
     * @inheritdoc
     */
    public function getAttributeDataWithCheckArray(): array
    {
        return array_replace_recursive(
            parent::getAttributeDataWithCheckArray(),
            [
                "{$this->getFrontendInput()}_with_required_fields" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_with_store_view_scope" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_with_global_scope" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_with_website_scope" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_with_attribute_code" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_with_unique_value" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_without_unique_value" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_with_enabled_add_to_column_options" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_without_enabled_add_to_column_options" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_with_enabled_use_in_filter_options" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
                "{$this->getFrontendInput()}_without_enabled_use_in_filter_options" => [
                    1 => [
                        'frontend_input' => 'select',
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function getOptionsDataArr(): array
    {
        return [
            [
                'optiontext' => [
                    'order' => [
                        'option_0' => '1',
                    ],
                    'value' => [
                        'option_0' => [
                            0 => 'Admin value description 1',
                            1 => 'Default store view value description 1',
                        ],
                    ],
                    'delete' => [
                        'option_0' => '',
                    ],
                ],
                'defaulttext' => [
                    0 => 'option_0',
                ],
                'swatchtext' => [
                    'value' => [
                        'option_0' => [
                            0 => 'Admin value 1',
                            1 => 'Default store view value 1',
                        ],
                    ],
                ],
            ],
            [
                'optiontext' => [
                    'order' => [
                        'option_1' => '2',
                    ],
                    'value' => [
                        'option_1' => [
                            0 => 'Admin value description 2',
                            1 => 'Default store view value description 2',
                        ],
                    ],
                    'delete' => [
                        'option_1' => '',
                    ],
                ],
                'swatchtext' => [
                    'value' => [
                        'option_1' => [
                            0 => 'Admin value 2',
                            1 => 'Default store view value 2',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getFrontendInput(): string
    {
        return Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT;
    }
}
