<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Output\ElementMapper;

use GraphQL\Type\Definition\OutputType;
use Magento\Framework\GraphQl\Config\Data\StructureInterface;

/**
 * {@inheritdoc}
 */
class FormatterComposite implements FormatterInterface
{
    /**
     * @var FormatterInterface[]
     */
    private $formatters;

    /**
     * @param FormatterInterface[] $formatters
     */
    public function __construct(array $formatters)
    {
        $this->formatters = $formatters;
    }

    /**
     * {@inheritDoc}
     */
    public function format(StructureInterface $typeStructure, OutputType $outputType)
    {
        $config = [
            'name' => $typeStructure->getName(),
            'description' => $typeStructure->getDescription()
        ];
        foreach ($this->formatters as $formatter) {
            $config = array_merge($config, $formatter->format($typeStructure, $outputType));
        }

        return $config;
    }
}
