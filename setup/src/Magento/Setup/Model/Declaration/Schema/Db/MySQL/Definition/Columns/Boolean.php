<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * As all MySQL aliases as BOOL or BOOLEAN are converted to TINYINT(1)
 * proposed to processed tinyint as boolean
 *
 * @inheritdoc
 */
class Boolean implements DbDefinitionProcessorInterface
{
    /**
     * Type with what we will persist column
     */
    const TYPE = 'BOOLEAN';

    /**
     * Type of integer that will be used in MySQL for boolean
     */
    const INTEGER_TYPE = 'tinyint';

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
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param Nullable $nullable
     * @param DefaultDefinition $defaultDefinition
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(
        Nullable $nullable,
        DefaultDefinition $defaultDefinition,
        BooleanUtils $booleanUtils
    ) {
        $this->nullable = $nullable;
        $this->defaultDefinition = $defaultDefinition;
        $this->booleanUtils = $booleanUtils;
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
            $data['type'] = strtolower(self::TYPE);
            $data['default'] = (bool) $data['default'];
            $data['unsigned'] = false; //For boolean we always do not want to have unsigned
        }

        return $data;
    }
}
