<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Output;

use Magento\Framework\GraphQl\Config\Element\Type as TypeElement;
use Magento\Framework\GraphQl\Schema\Type\ObjectType;

/**
 * 'output' type compatible with GraphQL schema generator.
 */
class OutputTypeObject extends ObjectType
{
    /**
     * @param ElementMapper $elementMapper
     * @param TypeElement $configElement
     */
    public function __construct(
        ElementMapper $elementMapper,
        TypeElement $configElement
    ) {
        parent::__construct($elementMapper->buildSchemaArray($configElement, $this));
    }
}
