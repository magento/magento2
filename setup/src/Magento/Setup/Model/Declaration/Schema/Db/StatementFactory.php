<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;

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
     * @var string
     */
    private $referenceClassName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $className
     * @param string $referenceClassName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $className = Statement::class,
        $referenceClassName = ReferenceStatement::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
        $this->referenceClassName = $referenceClassName;
    }

    /**
     * Create statemtnt object
     *
     * @param string $name
     * @param string $tableName
     * @param string $type
     * @param string $statement
     * @param string $resource
     * @param string|null $elementType
     * @return Statement
     */
    public function create(
        string $name,
        string $tableName,
        string $type,
        string $statement,
        string $resource,
        string $elementType = null
    ) {
        $className = $elementType === Reference::TYPE ? $this->referenceClassName : $this->className;
        return $this->objectManager->create($className, [
            'name' => $name,
            'tableName' => $tableName,
            'resource' => $resource,
            'type' => $type,
            'statement' => $statement
        ]);
    }
}
