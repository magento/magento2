<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for statement aggregator
 */
class StatementAggregatorFactory
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
    public function __construct(ObjectManagerInterface $objectManager, $className = StatementAggregator::class)
    {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * Create statemtnt aggregator object
     *
     * @return StatementAggregator
     */
    public function create()
    {
        return $this->objectManager->create($this->className);
    }
}
