<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\Setup\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\ColumnUnsignedAwareInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * Unsigned flag processor.
 * Unsigned can be used for all numeric types.
 *
 * @inheritdoc
 */
class Unsigned implements DbDefinitionProcessorInterface
{
    /**
     * Unsigned flag. Applicable only to numeric types.
     */
    const UNSIGNED_FLAG = 'unsigned';

    /**
     * @param ColumnUnsignedAwareInterface $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        return $column->isUnsigned() ? strtoupper(self::UNSIGNED_FLAG) : '';
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $data['unsigned'] = stripos($data['definition'], self::UNSIGNED_FLAG) !== false;
        return $data;
    }
}
