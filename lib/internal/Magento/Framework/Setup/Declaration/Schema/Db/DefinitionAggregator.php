<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db;

use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * Holds different definitions and apply them depends on column, constraint, index types.
 * Converts object to definition, and definition to array.
 *
 * @inheritdoc
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
            $data['default'] = $this->processDefaultValue($data['default']);
        }

        return $definitionProcessor->fromDefinition($data);
    }

    /**
     * Processes `$value` to be compatible with MySQL.
     *
     * @param string|null|bool $value
     * @return string|null|bool
     */
    protected function processDefaultValue($value)
    {
        //bail out if no default is set
        if ($value === null || $value === false) {
            return $value;
        }
        /*
         * MariaDB replaces some defaults by their respective functions, e.g. `DEFAULT CURRENT_TIMESTAMP` ends up being
         * `current_timestamp()`  in the information schema.
         */
        $value = strtr(
            $value,
            [
                'current_timestamp()' => 'CURRENT_TIMESTAMP',
                'curdate()' => 'CURRENT_DATE',
                'curtime()' => 'CURRENT_TIME',
            ]
        );

        /*
         * MariaDB replaces 0 defaults by 0000-00-00 00:00:00
         */
        $value = strtr(
            $value,
            ['0000-00-00 00:00:00' => '0']
        );
        //replace escaped single quotes
        $value = str_replace("'", "", $value);
        //unquote NULL literal
        if ($value === "NULL") {
            $value = null;
        }

        return $value;
    }
}
