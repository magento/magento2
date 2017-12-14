<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * @inheritdoc
 */
class Varchar implements DbSchemaProcessorInterface
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
        if (preg_match('/^(char|varchar|varbinary)\((\d+)\)/', $data['type'], $matches)) {
            $data['type'] = $matches[1];
            $data['length'] = $matches[2];
        }

        return $data;
    }
}
