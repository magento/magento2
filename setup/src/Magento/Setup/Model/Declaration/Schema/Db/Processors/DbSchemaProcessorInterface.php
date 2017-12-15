<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors;

use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Do processing strings to desired format:
 * For example, from VARCHAR(255) to:
 * 'type' => 'varchar'
 * 'length' => 255
 */
interface DbSchemaProcessorInterface
{
    /**
     * Check whether current processor can process element
     *
     * @param ElementInterface $element
     * @return mixed
     */
    public function canBeApplied(ElementInterface $element);

    /**
     * Output always will be SQL definition
     *
     * @param ElementInterface $element
     * @return string
     */
    public function toDefinition(ElementInterface $element);

    /**
     * Input always will be array of SQL definitions,
     * like:
     *  'type' => 'name VARCHAR(255)'\
     *  'nullable' => 'no'
     *
     * @param array $data
     * @return array
     */
    public function fromDefinition(array $data);
}
