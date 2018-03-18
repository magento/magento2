<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Enum;

use GraphQL\Type\Definition\EnumType;
use Magento\Framework\GraphQl\Config\Data\Enum as EnumStructure;

class Enum extends EnumType
{
    /**
     * Enum constructor.
     * @param EnumStructure $structure
     */
    public function __construct(EnumStructure $structure)
    {
        $config = [
            'name' => $structure->getName(),
        ];
        foreach ($structure->getValues() as $value) {
            $config['values'][$value->getValue()] = [
                'value' => $value->getValue()
            ];
        }
        parent::__construct($config);
    }
}
