<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Framework\DataObject;

class SelectAttribute extends Attribute
{
    private const DEFAULT_DATA = [
        'frontend_input' => 'select',
        'options' => [
            [
                'label' => 'option_1',
                'sort_order' => 0,
            ],
            [
                'label' => 'option_2',
                'sort_order' => 1,
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as \Magento\Catalog\Test\Fixture\Attribute::DEFAULT_DATA.
     * Additional fields:
     *  - $data['options']: Array of options.
     * Option ID can be retrieved as follows:
     * <pre>
     *  $attribute->getData('option_1')
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = $this->prepareData($data);

        $attribute =  parent::apply($data);

        // add options data to attribute data [option_label => option_id]
        $options = $attribute->getSource()->getAllOptions(false);
        $attribute->addData(array_column($options, 'value', 'label'));

        return $attribute;
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

        $options = [];
        $sortOrder = 0;
        foreach ($data['options'] as $option) {
            $options[] = [
                'label' => is_array($option) ? $option['label'] : $option,
                'sort_order' => $sortOrder++,
            ];
        }

        $data['options'] = $options;

        return $data;
    }
}
