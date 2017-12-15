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
     * @var Unsigned
     */
    private $unsigned;

    /**
     * @var \Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns\Boolean
     */
    private $boolean;

    /**
     * @var Nullable
     */
    private $nullable;

    /**
     * @var Identity
     */
    private $identity;

    /**
     * @param Unsigned $unsigned
     * @param bool $boolean
     * @param Nullable $nullable
     * @param Identity $identity
     */
    public function __construct(
        Unsigned $unsigned,
        Boolean $boolean,
        Nullable $nullable,
        Identity $identity
    ) {
        $this->unsigned = $unsigned;
        $this->boolean = $boolean;
        $this->nullable = $nullable;
        $this->identity = $identity;
    }

    /**
     * @inheritdoc
     */
    public function canBeApplied(ElementInterface $element)
    {
        return $element instanceof \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Integer;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Integer $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        return sprintf(
            '%s(%s) %s %s %s %s',
            $element->getElementType(),
            $element->getPadding(),
            $this->unsigned->toDefinition($element),
            $this->nullable->toDefinition($element),
            $element->getDefault(),
            $this->identity->toDefinition($element)
        );
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

            $data = $this->identity->fromDefinition($data);
            $data = $this->boolean->fromDefinition($data);
        }

        return $data;
    }
}
