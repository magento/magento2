<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Output;

use GraphQL\Type\Definition\InterfaceType;
use Magento\Framework\GraphQl\Config\Data\TypeInterface as TypeInterfaceStructure;

/**
 * Represents an interface type for GraphQL output
 */
class OutputInterfaceObject extends InterfaceType
{
    /**
     * @param ElementMapper $elementMapper
     * @param TypeInterfaceStructure $structure
     */
    public function __construct(
        ElementMapper $elementMapper,
        TypeInterfaceStructure $structure
    ) {
        parent::__construct($elementMapper->buildSchemaArray($structure, $this));
    }
}
