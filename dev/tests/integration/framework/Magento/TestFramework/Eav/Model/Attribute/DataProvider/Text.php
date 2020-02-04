<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Eav\Model\Attribute\DataProvider;

/**
 * Product attribute data for attribute with input type text.
 */
class Text extends AbstractBaseAttributeData
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaultAttributePostData['frontend_class'] = '';
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
                "{$this->getFrontendInput()}_with_input_validation" => [
                    array_merge($this->defaultAttributePostData, ['frontend_class' => 'validate-alpha']),
                ],
                "{$this->getFrontendInput()}_without_input_validation" => [
                    $this->defaultAttributePostData,
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getAttributeDataWithCheckArray(): array
    {
        return array_merge_recursive(
            parent::getAttributeDataWithCheckArray(),
            [
                "{$this->getFrontendInput()}_with_input_validation" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'frontend_class' => 'validate-alpha',
                    ],
                ],
                "{$this->getFrontendInput()}_without_input_validation" => [
                    [
                        'attribute_code' => 'test_attribute_name',
                        'frontend_class' => '',
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
        return 'text';
    }
}
