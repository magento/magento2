<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Api;

use Magento\Framework\ObjectManagerInterface;

/**
 * Api service factor
 */
class ServiceFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create Api service
     *
     * @param string $className
     * @param string $methodName
     * @return Service
     */
    public function create(string $className, string $methodName): Service
    {
        return $this->objectManager->create(
            Service::class,
            [
                'className' => $className,
                'methodName' => $methodName
            ]
        );
    }
}
