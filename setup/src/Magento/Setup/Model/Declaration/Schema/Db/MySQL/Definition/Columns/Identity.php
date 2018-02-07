<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\ColumnIdentityAwareInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Identity (auto_increment) column processor.
 *
 * @inheritdoc
 */
class Identity implements DbDefinitionProcessorInterface
{
    /**
     * Auto increment flag.
     */
    const IDENTITY_FLAG = 'auto_increment';

    /**
     * @param ColumnIdentityAwareInterface $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        return $column->isIdentity() ? strtoupper(self::IDENTITY_FLAG) : '';
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        if (!empty($data['extra']) && stripos($data['extra'], self::IDENTITY_FLAG) !== false) {
            $data['identity'] = true;
        }

        return $data;
    }
}
