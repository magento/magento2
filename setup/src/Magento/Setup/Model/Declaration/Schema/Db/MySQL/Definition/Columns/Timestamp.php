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
 * Process timestamp and find out it on_update and default values
 *
 * @inheritdoc
 */
class Timestamp implements DbDefinitionProcessorInterface
{
    /**
     * This timestamp can be used, when const value as DEFAULT 0 was passed
     */
    const CONST_DEFAULT_TIMESTAMP = '0000-00-00 00:00:00';

    /**
     * @var OnUpdate
     */
    private $onUpdate;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Nullable
     */
    private $nullable;

    /**
     * @param OnUpdate $onUpdate
     * @param Nullable $nullable
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(OnUpdate $onUpdate, Nullable $nullable, ResourceConnection $resourceConnection)
    {
        $this->onUpdate = $onUpdate;
        $this->resourceConnection = $resourceConnection;
        $this->nullable = $nullable;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        $nullable = $this->nullable->toDefinition($column);
        $default  = $column->getDefault() === 'NULL' ?
            '' : sprintf('DEFAULT %s', $column->getDefault());

        return sprintf(
            '%s %s %s %s %s',
            $this->resourceConnection->getConnection()->quoteIdentifier($column->getName()),
            $column->getType(),
            $nullable,
            $default,
            $this->onUpdate->toDefinition($column)
        );
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        if ($data['default'] === self::CONST_DEFAULT_TIMESTAMP) {
            $data['default'] = '0';
        }
        $data = $this->nullable->fromDefinition($data);
        $data = $this->onUpdate->fromDefinition($data);
        return $data;
    }
}
