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
     * Type with what we will persist column
     */
    const TYPE = 'BOOLEAN';

    /**
     * Type of integer that will be used in MySQL for boolean
     */
    const INTEGER_TYPE = 'tinyinteger';

    /**
     * Padding for integer described below
     */
    const INTEGER_PADDING = '1';

    /**
     * @var Nullable
     */
    private $nullable;

    /**
     * @var DefaultDefinition
     */
    private $defaultDefinition;

    /**
     * @param Nullable          $nullable
     * @param DefaultDefinition $defaultDefinition
     */
    public function __construct(Nullable $nullable, DefaultDefinition $defaultDefinition)
    {
        $this->nullable = $nullable;
        $this->defaultDefinition = $defaultDefinition;
    }

    /**
     * @inheritdoc
     */
    public function canBeApplied(ElementInterface $element)
    {
        return $element instanceof \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Boolean;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Boolean $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        return sprintf(
            '%s %s %s',
            self::TYPE,
            $this->nullable->toDefinition($element),
            $this->defaultDefinition->toDefinition($element)
        );
    }

    /**
     * Boolean is presented as tinyint(1) so we need to detect that value
     *
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        if ($data['type'] === self::INTEGER_TYPE && $data['padding'] === self::INTEGER_PADDING) {
            $data['type'] = 'boolean';
            $data['default'] = (bool) $data['default'];
            $data['unsigned'] = false; //For boolean we always do not want to have unsigned
        }

        return $data;
    }
}
