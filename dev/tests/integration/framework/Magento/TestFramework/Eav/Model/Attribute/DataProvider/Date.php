<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Eav\Model\Attribute\DataProvider;

/**
 * Product attribute data for attribute with input type date.
 */
class Date extends AbstractBaseAttributeData
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
                        'default_value_date' => '10/29/2019',
                    ]
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getAttributeDataWithCheckArray(): array
    {
        return array_replace_recursive(
            parent::getAttributeDataWithCheckArray(),
            [
                "{$this->getFrontendInput()}_with_default_value" => [
                    1 => [
                        'default_value' => '2019-10-29 00:00:00',
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
        return 'date';
    }
}
