<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Process integer type and separate it on type and padding
 *
 * @inheritdoc
 */
class Integer implements DbDefinitionProcessorInterface
{
    /**
     * @var Unsigned
     */
    private $unsigned;

    /**
     * @var \Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns\Boolean
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
     * @var DefaultDefinition
     */
    private $defaultDefinition;

    /**
     * @param Unsigned          $unsigned
     * @param bool              $boolean
     * @param Nullable          $nullable
     * @param Identity          $identity
     * @param DefaultDefinition $defaultDefinition
     */
    public function __construct(
        Unsigned $unsigned,
        Boolean $boolean,
        Nullable $nullable,
        Identity $identity,
        DefaultDefinition $defaultDefinition
    ) {
        $this->unsigned = $unsigned;
        $this->boolean = $boolean;
        $this->nullable = $nullable;
        $this->identity = $identity;
        $this->defaultDefinition = $defaultDefinition;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Integer $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        return sprintf(
            '%s(%s) %s %s %s %s',
            $element->getType(),
            $element->getPadding(),
            $this->unsigned->toDefinition($element),
            $this->nullable->toDefinition($element),
            $this->defaultDefinition->toDefinition($element),
            $this->identity->toDefinition($element)
        );
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $matches = [];
        if (preg_match('/^(big|small|tiny)?int\((\d+)\)/', $data['definition'], $matches)) {
            /**
             * match[1] - prefix
             * match[2] - padding, like 5 or 11
             */
            //Use shortcut for mediuminteger
            $data['padding'] = $matches[2];
            $data = $this->unsigned->fromDefinition($data);
            $data = $this->nullable->fromDefinition($data);
            $data = $this->identity->fromDefinition($data);
            $data = $this->boolean->fromDefinition($data);
        }

        return $data;
    }
}
