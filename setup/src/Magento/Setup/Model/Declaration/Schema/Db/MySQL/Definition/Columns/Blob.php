<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Process blob and text types
 *
 * @inheritdoc
 */
class Blob implements DbDefinitionProcessorInterface
{
    /**
     * @var Nullable
     */
    private $nullable;

    /**
     * Text constructor.
     *
     * @param Nullable $nullable
     */
    public function __construct(Nullable $nullable)
    {
        $this->nullable = $nullable;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Blob $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        return sprintf(
            '%s %s',
            $element->getType(),
            $this->nullable->toDefinition($element)
        );
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $matches = [];
        if (preg_match('/^(tiny|medium|long)(text|blob)\s(\d+)/', $data['definition'], $matches)) {
            $data['length'] = $matches[2];
        }

        return $data;
    }
}
