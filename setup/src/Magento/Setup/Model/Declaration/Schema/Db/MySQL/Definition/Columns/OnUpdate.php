<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * On update attribute is like trigger and can be used for many different columns
 *
 * @inheritdoc
 */
class OnUpdate implements DbDefinitionProcessorInterface
{
    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        if ($column instanceof \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp) {
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
