<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Eav\Model\Attribute\DataProvider;

/**
 * Base POST data for create attribute with options.
 */
abstract class AbstractAttributeDataWithOptions extends AbstractBaseAttributeData
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaultAttributePostData['serialized_options_arr'] = $this->getOptionsDataArr();
        $this->defaultAttributePostData['is_filterable'] = '0';
        $this->defaultAttributePostData['is_filterable_in_search'] = '0';
    }

    /**
     * @inheritdoc
     */
    public function getAttributeData(): array
    {
        $result = parent::getAttributeData();
        unset($result["{$this->getFrontendInput()}_with_default_value"]);
        unset($result["{$this->getFrontendInput()}_without_default_value"]);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeDataWithErrorMessage(): array
    {
        $wrongSerializeMessage = 'The attribute couldn\'t be saved due to an error. Verify your information and ';
        $wrongSerializeMessage .= 'try again. If the error persists, please try again later.';

        return array_replace_recursive(
            parent::getAttributeDataWithErrorMessage(),
            [
                "{$this->getFrontendInput()}_with_wrong_serialized_options" => [
                    array_merge(
                        $this->defaultAttributePostData,
                        [
                            'serialized_options_arr' => [],
                            'serialized_options' => '?.\\//',
                        ]
                    ),
                    (string)__($wrongSerializeMessage)
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getAttributeDataWithCheckArray(): array
    {
        $result = parent::getAttributeDataWithCheckArray();
        unset($result["{$this->getFrontendInput()}_with_default_value"]);
        unset($result["{$this->getFrontendInput()}_without_default_value"]);

        return $result;
    }

    /**
     * Return attribute options data.
     *
     * @return array
     */
    protected function getOptionsDataArr(): array
    {
        return [
            [
                'option' => [
                    'order' => [
                        'option_0' => '1',
                    ],
                    'value' => [
                        'option_0' => [
                            'Admin value 1',
                            'Default store view value 1',
                        ],
                    ],
                    'delete' => [
                        'option_0' => '',
                    ],
                ],
            ],
            [
                'option' => [
                    'order' => [
                        'option_1' => '2',
                    ],
                    'value' => [
                        'option_1' => [
                            'Admin value 2',
                            'Default store view value 2',
                        ],
                    ],
                    'delete' => [
                        'option_1' => '',
                    ],
                ],
            ],
        ];
    }
}
