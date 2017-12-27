<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * This class holds different definitions and apply them depends on column, constraint, index types
 *
 * It can convert object to definition, and definition to array
 * @inheritdoc
 */
class DefinitionAggregator implements DbDefinitionProcessorInterface
{
    /**
     * @var DbDefinitionProcessorInterface[]
     */
    private $definitionProcessors;

    /**
     * @param DbDefinitionProcessorInterface[] $definitionProcessors
     */
    public function __construct(array $definitionProcessors)
    {
        $this->definitionProcessors = $definitionProcessors;
    }

    /**
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        $type = $element->getType();
        if (!isset($this->definitionProcessors[$type])) {
            throw new \InvalidArgumentException(
                sprintf("Cannot process object to definition for type %s", $type)
            );
        }

        $definitionProcessor = $this->definitionProcessors[$type];
        return $definitionProcessor->toDefinition($element);
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $type = $data['type'];
        if (!isset($this->definitionProcessors[$type])) {
            throw new \InvalidArgumentException(
                sprintf("Cannot process definition to array for type %s", $type)
            );
        }

        $definitionProcessor = $this->definitionProcessors[$type];
        return $definitionProcessor->fromDefinition($data);
    }
}
