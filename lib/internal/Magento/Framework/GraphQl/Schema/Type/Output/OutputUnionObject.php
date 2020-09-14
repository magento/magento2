<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Output;

use Magento\Framework\GraphQl\Config\Element\UnionType as UnionElement;
use Magento\Framework\GraphQl\Schema\Type\UnionType;

/**
 * The 'union' type compatible with GraphQL schema generator.
 */
class OutputUnionObject extends UnionType
{
    /**
     * @param ElementMapper $elementMapper
     * @param UnionElement $configElement
     */
    public function __construct(
        ElementMapper $elementMapper,
        UnionElement $configElement
    ) {
        parent::__construct($elementMapper->buildSchemaArray($configElement, $this));
    }
}
