<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Output;

use Magento\Framework\GraphQl\Config\Element\InterfaceType as InterfaceElement;
use Magento\Framework\GraphQl\Schema\Type\InterfaceType;

/**
 * 'interface' type compatible with GraphQL schema generator.
 */
class OutputInterfaceObject extends InterfaceType
{
    /**
     * @param ElementMapper $elementMapper
     * @param InterfaceElement $configElement
     */
    public function __construct(
        ElementMapper $elementMapper,
        InterfaceElement $configElement
    ) {
        parent::__construct($elementMapper->buildSchemaArray($configElement, $this));
    }
}
