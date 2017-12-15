<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * On update attribute is like trigger and can be used for many different columns
 *
 * @inheritdoc
 */
class OnUpdate implements DbSchemaProcessorInterface
{
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
        $matches = [];
        if (preg_match('/^(?:on update)\s([\_\-\s\w\d]+)/', $data['extra'], $matches)) {
            $data['on_update'] = $matches[1];
        }

        return $data;
    }
}
