<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Ui\Component\Listing\Filter;

use Magento\Customer\Model\Config\Source\FilterConditionType;

class TextFilterConfigProvider implements FilterConfigProviderInterface
{
    private const FILTER_CONDITION_TYPE = 'grid_filter_condition_type';

    private const FILTER_CONDITION_TYPE_MAP = [
        FilterConditionType::FULL_MATCH => 'eq',
        FilterConditionType::PARTIAL_MATCH => 'like',
        FilterConditionType::PREFIX_MATCH => 'like',
    ];

    private const FILTER_CONDITION_TYPE_VALUE_EXPRESSION_MAP = [
        FilterConditionType::PARTIAL_MATCH => '%%%s%%',
        FilterConditionType::PREFIX_MATCH => '%s%%',
    ];

    /**
     * @inheritdoc
     */
    public function getConfig(array $attributeData): array
    {
        $value = $attributeData[self::FILTER_CONDITION_TYPE] ?? FilterConditionType::PARTIAL_MATCH;

        return [
            'conditionType' => self::FILTER_CONDITION_TYPE_MAP[$value]
                ?? self::FILTER_CONDITION_TYPE_MAP[FilterConditionType::PARTIAL_MATCH],
            'valueExpression' => self::FILTER_CONDITION_TYPE_VALUE_EXPRESSION_MAP[$value] ?? null,
        ];
    }
}
