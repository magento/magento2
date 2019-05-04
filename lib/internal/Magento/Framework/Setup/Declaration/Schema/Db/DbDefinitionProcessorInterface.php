<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db;

use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * Do processing strings to desired format:
 * For example, from VARCHAR(255) to:
 * 'type' => 'varchar'
 * 'length' => 255
 */
interface DbDefinitionProcessorInterface
{
    /**
     * Output always will be SQL definition.
     *
     * @param  ElementInterface $column
     * @return string
     */
    public function toDefinition(ElementInterface $column);

    /**
     * Input always will be array of SQL definitions,
     * like:
     *  'type' => 'name VARCHAR(255)'\
     *  'nullable' => 'no'
     *
     * @param  array $data
     * @return array
     */
    public function fromDefinition(array $data);
}
