<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Constraints;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * Internal key (index) processor.
 *
 * Detect primary or unique constraints and map them to appropriate format.
 *
 * @inheritdoc
 */
class Internal implements DbDefinitionProcessorInterface
{
    /**
     * Name of Primary Key.
     */
    const PRIMARY_NAME = 'PRIMARY';

    /**
     * Primary key statement.
     */
    const PRIMARY_KEY_NAME = 'PRIMARY KEY';

    /**
     * Unique key statement.
     */
    const UNIQUE_KEY_NAME = 'UNIQUE KEY';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param \Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Internal $constraint
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $constraint)
    {
        $adapter = $this->resourceConnection->getConnection(
            $constraint->getTable()->getResource()
        );
        $columnsList = array_map(
            function ($columnName) use ($adapter) {
                return $adapter->quoteIdentifier($columnName);
            },
            $constraint->getColumnNames()
        );
        $isPrimary = $constraint->getType() === 'primary';

        return sprintf(
            'CONSTRAINT %s %s (%s)',
            $isPrimary ? '' : $adapter->quoteIdentifier($constraint->getName()),
            $isPrimary ? 'PRIMARY KEY' : 'UNIQUE KEY',
            implode(',', $columnsList)
        );
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        return [
            'name' => $data['Key_name'],
            'column' => [
                $data['Column_name'] => $data['Column_name']
            ],
            'type' => $data['Key_name'] === self::PRIMARY_NAME ? 'primary' : 'unique'
        ];
    }
}
