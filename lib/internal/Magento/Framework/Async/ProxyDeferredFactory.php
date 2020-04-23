<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Async;

use Magento\Framework\ObjectManagerInterface;

/**
 * Create deferred proxy for a class.
 */
class ProxyDeferredFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create deferred proxy for given class.
     *
     * @param string $className
     * @param DeferredInterface $deferred
     * @return object Instance of $className.
     */
    public function createFor(string $className, DeferredInterface $deferred)
    {
        return $this->objectManager->create($className .'\\ProxyDeferred', ['deferred' => $deferred]);
    }
}
