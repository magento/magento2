<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Output\ElementMapper\Formatter;

use GraphQL\Type\Definition\OutputType;
use Magento\Framework\GraphQl\Config\Data\StructureInterface;
use Magento\Framework\GraphQl\Type\Output\ElementMapper\FormatterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\GraphQl\Config\Data\InterfaceType;

/**
 * Add resolveType field to schema config array based on type structure properties.
 */
class ResolveType implements FormatterInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritDoc}
     */
    public function format(StructureInterface $typeStructure, OutputType $outputType)
    {
        $config = [];
        if ($typeStructure instanceof InterfaceType) {
            $typeResolver = $this->objectManager->create($typeStructure->getTypeResolver());
            $config['resolveType'] = function ($value) use ($typeResolver) {
                return $typeResolver->resolveType($value);
            };
        }

        return $config;
    }
}
