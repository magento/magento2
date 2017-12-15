<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\ColumnNullableAwareInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Go through all columns that can be nullable and modify them
 *
 * @inheritdoc
 */
class Nullable implements DbSchemaProcessorInterface
{
    /**
     * MySQL flag, that says that we need to use unsigned numbers.
     * Can be applicable only for number types
     */
    const UNSIGNED_FLAG = 'unsigned';

    /**
     * @param ColumnNullableAwareInterface $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        if ($element instanceof ColumnNullableAwareInterface) {
            return $element->isNullable() ? 'NULL' : 'NOT NULL';
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function canBeApplied(ElementInterface $element)
    {
        return false;
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
        $data['nullable'] = $this->processNullable($data['nullable']);
        return $data;
    }
}
