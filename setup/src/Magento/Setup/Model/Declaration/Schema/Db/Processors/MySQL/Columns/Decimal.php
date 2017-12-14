<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * @inheritdoc
 */
class Decimal implements DbSchemaProcessorInterface
{
    /**
     * @var Unsigned
     */
    private $unsigned;

    /**
     * @param Unsigned $unsigned
     */
    public function __construct(Unsigned $unsigned)
    {
        $this->unsigned = $unsigned;
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
        if (preg_match('/^(float|decimal|double)\((\d+),(\d+)\)/', $data['type'], $matches)) {
            /**
             * match[1] - type
             * match[2] - precision
             * match[3] - scale
             */
            $data = $this->unsigned->fromDefinition($data);
            $data['type'] = $matches[1];

            if (isset($matches[2]) && isset($matches[3])) {
                $data['scale'] = $matches[3];
                $data['precission'] = $matches[2];
            }
        }

        return $data;
    }
}
