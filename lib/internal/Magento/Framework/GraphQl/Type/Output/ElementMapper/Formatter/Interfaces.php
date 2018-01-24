<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Output\ElementMapper\Formatter;

use GraphQL\Type\Definition\OutputType;
use Magento\Framework\GraphQl\Config\Data\StructureInterface;
use Magento\Framework\GraphQl\Config\Data\Type;
use Magento\Framework\GraphQl\Type\Output\ElementMapper\FormatterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\GraphQl\Type\Output\OutputMapper;

/**
 * Add interfaces implemented by type if configured.
 */
class Interfaces implements FormatterInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var OutputMapper
     */
    private $outputMapper;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager, OutputMapper $outputMapper)
    {
        $this->objectManager = $objectManager;
        $this->outputMapper = $outputMapper;
    }

    /**
     * {@inheritDoc}
     */
    public function format(StructureInterface $typeStructure, OutputType $outputType)
    {
        $config = [];
        if ($typeStructure instanceof Type && !empty($typeStructure->getInterfaces())) {
            $interfaces = [];
            foreach ($typeStructure->getInterfaces() as $interface) {
                $interfaces[$interface['interface']] = $this->outputMapper->getInterface($interface['interface']);
            }
            $config['interfaces'] = $interfaces;
        }

        return $config;
    }
}
