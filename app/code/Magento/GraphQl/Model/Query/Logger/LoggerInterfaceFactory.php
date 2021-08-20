<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Query\Logger;

use Magento\Framework\ObjectManagerInterface;

/**
 * GraphQl logger interface factory
 */
class LoggerInterfaceFactory
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters.
     *
     * @param string $className
     * @param array $data
     * @return LoggerInterface
     */
    public function create($className, array $data = [])
    {
        return $this->objectManager->create($className, $data);
    }
}
