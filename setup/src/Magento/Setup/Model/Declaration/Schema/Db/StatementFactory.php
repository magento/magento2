<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

use Magento\Framework\ObjectManagerInterface;

/**
 * Statement object aggregates different SQL statements and run all of them for one table
 */
class StatementFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $className;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $className
     */
    public function __construct(ObjectManagerInterface $objectManager, $className = Statement::class)
    {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * Create statemtnt object
     *
     * @param string $tableName
     * @param string $type
     * @param string $statement
     * @param string $resource
     * @return Statement
     */
    public function create(string $tableName, string $type, string $statement, string $resource)
    {
        return $this->objectManager->create($this->className, [
            'tableName' => $tableName,
            'resource' => $resource,
            'type' => $type,
            'statement' => $statement
        ]);
    }
}
