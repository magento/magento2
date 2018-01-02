<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\App\ResourceConnection;
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
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Text constructor.
     *
     * @param Nullable $nullable
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(Nullable $nullable, ResourceConnection $resourceConnection)
    {
        $this->nullable = $nullable;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Blob $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        return sprintf(
            '%s %s %s',
            $this->resourceConnection->getConnection()->quoteIdentifier($column->getName()),
            $column->getType(),
            $this->nullable->toDefinition($column)
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
