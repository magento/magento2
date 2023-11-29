<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Fixture\Data;

use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\Framework\Serialize\Serializer\Json;

class ConditionsSerializer
{
    /**
     * @var Json
     */
    private Json $json;

    /**
     * @param Json $json
     */
    public function __construct(
        Json $json
    ) {
        $this->json = $json;
    }

    /**
     * Normalizes and serializes conditions data
     *
     * @param array $data
     * @return string
     */
    public function serialize(array $data): string
    {
        return $this->json->serialize($this->normalize($data));
    }

    /**
     * Normalizes conditions data
     *
     * @param array $data
     * @return array
     */
    private function normalize(array $data) : array
    {
        $conditions = $data;
        if (array_is_list($conditions)) {
            $conditions = [
                'conditions' => $conditions,
            ];
        }
        $conditions += [
            'type' => Combine::class,
            'attribute' => null,
            'value' => true,
            'operator' => null,
            'aggregator' => 'all',
            'is_value_processed' => null,
            'conditions' => [

            ],
        ];
        $subConditions = $conditions['conditions'];
        $conditions['conditions'] = [];

        foreach ($subConditions as $condition) {
            if (isset($condition['conditions']) && array_is_list($condition)) {
                $condition = $this->normalize($condition);
            } else {
                $condition += [
                    'type' => Product::class,
                    'attribute' => null,
                    'value' => null,
                    'operator' => '==',
                    'is_value_processed' => false,
                ];
            }

            $conditions['conditions'][] = $condition;
        }
        return $conditions;
    }
}
