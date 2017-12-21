<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\ColumnUnsignedAwareInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Unsigned can be used for all numeric types
 *
 * @inheritdoc
 */
class Unsigned implements DbSchemaProcessorInterface
{
    /**
     * MySQL flag, that says that we need to use unsigned numbers.
     * Can be applicable only for number types
     */
    const UNSIGNED_FLAG = 'unsigned';

    /**
     * @param ColumnUnsignedAwareInterface $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        return $element->isUnsigned() ? 'UNSIGNED' : '';
    }

    /**
     * @inheritdoc
     */
    public function canBeApplied(ElementInterface $element)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $data['unsigned'] = strpos($data['type'], self::UNSIGNED_FLAG) !== false;
        return $data;
    }
}
