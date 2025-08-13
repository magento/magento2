<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Reference;

/**
 * Statement factory.
 * Statement aggregates SQL statements and executes them for one table.
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
     * Constructor.
     *
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
     * Create statement object.
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
        ?string $elementType = null
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
