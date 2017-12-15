<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Unsigned can be used for all numeric types
 *
 * @inheritdoc
 */
class Unsigned implements DbSchemaProcessorInterface
{
    /**
     * MyMySQL flag, that says that we need to use unsigned numbers.
     * Can be applicable only for number types
     */
    const UNSIGNED_FLAG = 'unsigned';

    /**
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        if (strpos(self::UNSIGNED_FLAG, $data['type']) !== false) {
            $data['unsigned'] = true;
        } else {
            $data['unsigned'] = false;
        }

        return $data;
    }
}
