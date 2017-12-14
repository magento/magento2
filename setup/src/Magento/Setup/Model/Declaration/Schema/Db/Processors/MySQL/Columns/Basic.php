<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * We always have 6 fields and we need to process all six of them
 * @inheritdoc
 */
class Basic implements DbSchemaProcessorInterface
{
    /**
     * Token names that are mapped to response of MyMySQL describe command
     */
    private static $tokens = [
        0 => 'name',
        1 => 'type',
        2 => 'nullable',
        3 => 'key',
        4 => 'default',
        5 => 'extra'
    ];

    /**
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        return '';
    }

    /**
     * Convert MySQL nullable string value into boolean
     *
     * @param string $nullableValue
     * @return bool
     */
    private function processNullable($nullableValue)
    {
        return strtolower($nullableValue) === 'yes' ? true : false;
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $data = array_combine(array_values(self::$tokens), array_values($data));
        $data['type'] = strtolower($data['type']);
        $data['nullable'] = $this->processNullable($data['nullable']);
        unset($data['key']); //we do not need key, as it will be calculated from indexes
        return $data;
    }
}
