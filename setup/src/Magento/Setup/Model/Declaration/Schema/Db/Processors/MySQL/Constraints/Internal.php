<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Constraints;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Detect primary or unique constraints
 *
 * @inheritdoc
 */
class Internal implements DbSchemaProcessorInterface
{
    /**
     * Name of Primary Key
     */
    const PRIMARY_NAME = 'PRIMARY';

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
        return [
            'name' => $data['Key_name'],
            'column' => [
                $data['Column_name'] => $data['Column_name']
            ],
            'type' => $data['Key_name'] === self::PRIMARY_NAME ? 'primary' : 'unique'
        ];
    }
}
