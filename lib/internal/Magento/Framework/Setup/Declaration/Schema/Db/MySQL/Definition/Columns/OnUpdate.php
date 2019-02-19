<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\Setup\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * On update statement processor.
 *
 * @inheritdoc
 */
class OnUpdate implements DbDefinitionProcessorInterface
{
    /**
     * @param \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Timestamp $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        if ($column instanceof \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Timestamp) {
            return $column->getOnUpdate() ?
                'ON UPDATE CURRENT_TIMESTAMP' : '';
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $matches = [];
        if (preg_match('/^(?:on update)\s([\_\-\s\w\d]+)/', $data['extra'], $matches)) {
            $data['on_update'] = true;
        }

        return $data;
    }
}
