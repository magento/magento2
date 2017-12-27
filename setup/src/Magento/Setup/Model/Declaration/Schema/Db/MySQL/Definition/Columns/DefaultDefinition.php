<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\ColumnDefaultAwareInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Cast from and to default definitions
 *
 * @inheritdoc
 */
class DefaultDefinition implements DbDefinitionProcessorInterface
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
    public function fromDefinition(array $data)
    {
        return $data;
    }
}
