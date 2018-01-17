<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Output\ElementMapper;

use Magento\Framework\GraphQl\Config\Data\StructureInterface;
use GraphQL\Type\Definition\OutputType;

/**
 * Formats particular elements of a passed in type structure to corresponding array structure.
 */
interface FormatterInterface
{
    /**
     * Format specific type structure elements to GraphQL-readable array.
     *
     * @param StructureInterface $typeStructure
     * @param OutputType $outputType
     * @return array
     */
    public function format(StructureInterface $typeStructure, OutputType $outputType);
}
