<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\DB;

/**
 * Class QueryFactory
 */
class QueryFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create Query object
     *
     * @param string $className
     * @param array $arguments
     * @return QueryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($className, array $arguments = [])
    {
        $query = $this->objectManager->create($className, $arguments);
        if (!$query instanceof QueryInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase(
                    '%1 doesn\'t implement \Magento\Framework\DB\QueryInterface',
                    [$className]
                )
            );
        }
        return $query;
    }
}
