<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Enum;

use Magento\Framework\GraphQl\Config\Element\Enum as EnumElement;
use Magento\Framework\GraphQl\Schema\Type\EnumType;

/**
 * Object representation of a GraphQL enum field
 */
class Enum extends EnumType
{
    /**
     * @param EnumElement $configElement
     */
    public function __construct(EnumElement $configElement)
    {
        $config = [
            'name' => $configElement->getName(),
            'description' => $configElement->getDescription()
        ];

        if (empty($configElement->getValues())) {
            $config['values'] = [];
        }

        foreach ($configElement->getValues() as $value) {
            $config['values'][$value->getValue()] = [
                'value' => $value->getValue(),
                'description' => $value->getDescription(),
                'deprecationReason' => $value->getDeprecatedReason() ?: null
            ];
        }
        parent::__construct($config);
    }
}
