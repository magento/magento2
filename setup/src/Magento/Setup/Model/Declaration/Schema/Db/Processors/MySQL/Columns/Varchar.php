<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Varbinary;
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
     * @param Nullable $nullable
     */
    public function __construct(Nullable $nullable)
    {
        $this->nullable = $nullable;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Varchar $element
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Varbinary $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        return sprintf(
            '%s(%s) %s %s',
            $element->getElementType(),
            $element->getLength(),
            $this->nullable->toDefinition($element),
            $element->getDefault()
        );
    }

    /**
     * @inheritdoc
     */
    public function canBeApplied(ElementInterface $element)
    {
        return $element instanceof \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Varchar ||
               $element instanceof Varbinary;
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
