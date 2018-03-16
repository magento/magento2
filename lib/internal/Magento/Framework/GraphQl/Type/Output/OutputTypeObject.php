<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Output;

use GraphQL\Type\Definition\ObjectType;
use Magento\Framework\GraphQl\Config\Data\Type as TypeStructure;

/**
 * Represents a concrete output type for a GraphQL response.
 */
class OutputTypeObject extends ObjectType
{
    /**
     * @param ElementMapper $elementMapper
     * @param TypeStructure $structure
     */
    public function __construct(
        ElementMapper $elementMapper,
        TypeStructure $structure
    ) {
        parent::__construct($elementMapper->buildSchemaArray($structure, $this));
    }
}
