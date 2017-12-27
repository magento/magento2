<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * @inheritdoc
 */
class Varchar implements DbSchemaProcessorInterface
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
    public function canBeApplied(ElementInterface $element)
    {
        return $element instanceof \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Varchar;
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $matches = [];
        if (preg_match('/^(char|varchar|varbinary)\((\d+)\)/', $data['type'], $matches)) {
            $data['type'] = $matches[1];
            $data['length'] = $matches[2];
        }

        return $data;
    }
}
