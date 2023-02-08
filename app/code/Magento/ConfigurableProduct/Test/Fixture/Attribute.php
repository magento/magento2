<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Fixture;

use Magento\Framework\DataObject;

class Attribute extends \Magento\Catalog\Test\Fixture\Attribute
{
    private const DEFAULT_DATA = [
        'frontend_input' => 'select',
        'options' => [
            [
                'label' => 'option1%uniqid%',
                'sort_order' => 0,
            ],
            [
                'label' => 'option2%uniqid%',
                'sort_order' => 1,
            ]
        ],
        'scope' => 'global',
    ];

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = $this->prepareData($data);

        return parent::apply($data);
    }

    /**
     * Prepare attribute data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);

        return $data;
    }
}
