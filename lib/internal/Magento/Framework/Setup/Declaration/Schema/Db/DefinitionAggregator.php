<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db;

use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * Holds different definitions and apply them depends on column, constraint, index types.
 */
class DefinitionAggregator implements DbDefinitionProcessorInterface
{
    /**
     * @var DbDefinitionProcessorInterface[]
     */
    private $definitionProcessors;

    /**
     * Constructor.
     *
     * @param DbDefinitionProcessorInterface[] $definitionProcessors
     */
    public function __construct(array $definitionProcessors)
    {
        $this->definitionProcessors = $definitionProcessors;
    }

    /**
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        $type = $column->getType();
        if (!isset($this->definitionProcessors[$type])) {
            throw new \InvalidArgumentException(
                sprintf("Cannot process object to definition for type %s", $type)
            );
        }

        $definitionProcessor = $this->definitionProcessors[$type];
        return $definitionProcessor->toDefinition($column);
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
        if (isset($data['default'])) {
            $data['default'] = $this->processDefaultValue($data);
        }

        return $definitionProcessor->fromDefinition($data);
    }

    /**
     * Processes `$value` to be compatible with MySQL.
     *
     * @param array $data
     * @return string|null|bool
     */
    protected function processDefaultValue(array $data)
    {
        $defaultValue = $data['default'];
        if ($defaultValue === null || $data['default'] === false) {
            return $defaultValue;
        }
        if ($defaultValue === "NULL") {
            return null;
        }
        if ($defaultValue === "'NULL'") {
            return "NULL";
        }
        /*
         * MariaDB replaces some defaults by their respective functions, e.g. `DEFAULT CURRENT_TIMESTAMP` ends up being
         * `current_timestamp()`  in the information schema.
         */
        $defaultValue = strtr(
            $defaultValue,
            [
                'current_timestamp()' => 'CURRENT_TIMESTAMP',
                'curdate()' => 'CURRENT_DATE',
                'curtime()' => 'CURRENT_TIME'
            ]
        );
        //replace escaped single quotes
        $defaultValue = str_replace("'", "", $defaultValue);

        return $defaultValue;
    }
}
