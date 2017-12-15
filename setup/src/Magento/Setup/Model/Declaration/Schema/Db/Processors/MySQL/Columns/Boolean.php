<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * As all MySQL aliases as BOOL or BOOLEAN are converted to TINYINT(1)
 * proposed to processed tinyint as boolean
 *
 * @inheritdoc
 */
class Boolean implements DbSchemaProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        return '';
    }

    /**
     * Boolean is presented as tinyint(1) so we need to detect that value
     *
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        if ($data['type'] === 'tinyinteger' && $data['padding'] === '1') {
            $data['type'] = 'boolean';
            $data['default'] = (bool) $data['default'];
        }

        return $data;
    }
}
