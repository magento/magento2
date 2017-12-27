<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\ColumnUnsignedAwareInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Unsigned can be used for all numeric types
 *
 * @inheritdoc
 */
class Unsigned implements DbDefinitionProcessorInterface
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
        return $element->isUnsigned() ? strtoupper(self::UNSIGNED_FLAG) : '';
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $data['unsigned'] = strpos($data['definition'], self::UNSIGNED_FLAG) !== false;
        return $data;
    }
}
