<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Process 3 different types: char, varchar, varbinary
 *
 * @inheritdoc
 */
class Varchar implements DbDefinitionProcessorInterface
{
    /**
     * @var Nullable
     */
    private $nullable;

    /**
     * @var DefaultDefinition
     */
    private $defaultDefinition;

    /**
     * @param Nullable          $nullable
     * @param DefaultDefinition $defaultDefinition
     */
    public function __construct(Nullable $nullable, DefaultDefinition $defaultDefinition)
    {
        $this->nullable = $nullable;
        $this->defaultDefinition = $defaultDefinition;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Varchar   $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        return sprintf(
            '%s(%s) %s %s',
            $element->getType(),
            $element->getLength(),
            $this->nullable->toDefinition($element),
            $this->defaultDefinition->toDefinition($element)
        );
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $matches = [];
        if (preg_match('/^(char|varchar|varbinary)\((\d+)\)/', $data['definition'], $matches)) {
            $data['length'] = $matches[2];
        }

        return $data;
    }
}
