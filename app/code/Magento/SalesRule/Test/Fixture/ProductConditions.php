<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class ProductConditions implements DataFixtureInterface
{
    public const DEFAULT_DATA = [
        'type' => Combine::class,
        'attribute' => null,
        'operator' => null,
        'value' => true,
        'is_value_processed' => null,
        'aggregator' => 'all',
        'conditions' => [

        ],
    ];

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        DataObjectFactory  $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as ProductConditions::DEFAULT_DATA.
     * - $data['conditions']: An array of conditions
     *      - ProductConditions
     *      - ProductCondition
     */
    public function apply(array $data = []): ?DataObject
    {
        return $this->dataObjectFactory->create(['data' => $this->prepareData($data)]);
    }

    /**
     * Prepare product conditions data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        $conditions = [];

        foreach ($data['conditions'] as $condition) {
            $conditionData = $condition instanceof DataObject ? $condition->toArray() : $condition;
            if (!isset($condition['conditions'])) {
                $conditionData += ProductCondition::DEFAULT_DATA;
            }
            $conditions[] = $conditionData;
        }
        $data['conditions'] = $conditions;

        return $data;
    }
}
