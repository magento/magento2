<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * We always have 6 fields and we need to process all six of them
 * For columns we do not need 'key'
 * Also we need to make nullable and type in lower case
 *
 * @inheritdoc
 */
class Basic implements DbSchemaProcessorInterface
{
    /**
     * Token names that are mapped to response of MyMySQL describe command
     */
    private static $tokens = [
        0 => 'name',
        1 => 'type',
        2 => 'nullable',
        3 => 'key',
        4 => 'default',
        5 => 'extra'
    ];

    /**
     * @var Unsigned
     */
    private $unsigned;

    /**
     * @var Nullable
     */
    private $nullable;

    /**
     * @param Unsigned $unsigned
     * @param Nullable $nullable
     */
    public function __construct(Unsigned $unsigned, Nullable $nullable)
    {
        $this->unsigned = $unsigned;
        $this->nullable = $nullable;
    }

    /**
     * Basic type can not process any type of elements
     *
     * @param  ElementInterface $element
     * @return bool
     */
    public function canBeApplied(ElementInterface $element)
    {
        return false;
    }

    /**
     * Column definition sequence:
     *  - type
     *  - padding/scale/precision
     *  - unsigned
     *  - nullable
     *  - default
     *  - identity
     *  - comment
     *  - after-comment
     *
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        throw new \LogicException("Basic processor, can`t convert any element to definition");
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $data = array_combine(array_values(self::$tokens), array_values($data));
        $data['type'] = strtolower($data['type']);

        $data = $this->nullable->fromDefinition($data);

        unset($data['key']); //we do not need key, as it will be calculated from indexes
        return $data;
    }
}
