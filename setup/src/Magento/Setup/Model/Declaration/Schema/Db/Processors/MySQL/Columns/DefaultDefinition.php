<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\ColumnDefaultAwareInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Cast from and to default definitions
 *
 * @inheritdoc
 */
class DefaultDefinition implements DbSchemaProcessorInterface
{
    /**
     * @param ColumnDefaultAwareInterface $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        return $element->getDefault() !== null && $element->getDefault() !== '' ?
            sprintf('DEFAULT %s', $this->defaultStringify($element->getDefault())) : '';
    }

    /**
     * Stringify default before we will collect definition
     *
     * @param  mixed $default
     * @return string
     */
    private function defaultStringify($default)
    {
        if ($default === true) {
            $default = 'TRUE';
        } elseif ($default === false) {
            $default = 'FALSE';
        }

        return $default;
    }

    /**
     * @inheritdoc
     */
    public function canBeApplied(ElementInterface $element)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        return $data;
    }
}
