<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Process integer type and separate it on type and padding
 *
 * @inheritdoc
 */
class Integer implements DbSchemaProcessorInterface
{
    /**
     * MyMySQL flag, that says that we need to increment field, each time when we add new row
     */
    const IDENTITY_FLAG = 'auto_increment';

    /**
     * @var Unsigned
     */
    private $unsigned;

    /**
     * @var \Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns\Boolean
     */
    private $boolean;

    /**
     * @param Unsigned $unsigned
     * @param bool $boolean
     */
    public function __construct(Unsigned $unsigned, Boolean $boolean)
    {
        $this->unsigned = $unsigned;
        $this->boolean = $boolean;
    }

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
        if (preg_match('/^(big|medium|small|tiny)?int\((\d+)\)/', $data['type'], $matches)) {
            /**
             * match[0] - all
             * match[1] - prefix
             * match[2] - padding, like 5 or 11
             */
            $data = $this->unsigned->fromDefinition($data);
            $data['type'] = sprintf("%sinteger", $matches[1]);
            //Use shortcut for mediuminteger
            $data['type'] = $data['type'] === 'mediuminteger' ? 'integer' : $data['type'];

            if (isset($matches[2])) {
                $data['padding'] = $matches[2];
            }

            if (!empty($data['extra']) && strpos(self::IDENTITY_FLAG, $data['extra']) !== false) {
                $data['identity'] = true;
            }

            $data = $this->boolean->fromDefinition($data);
        }

        return $data;
    }
}
