<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Process blob and text types
 *
 * @inheritdoc
 */
class Text implements DbSchemaProcessorInterface
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
        if (preg_match('/^(tiny|medium|long)(text|blob)\s(\d+)/', $data['type'], $matches)) {
            if (isset($matches[0])) {
                $data['type'] = $matches[0] . $matches[1];
                $data['length'] = $matches[2];
            }
        }

        return $data;
    }
}
