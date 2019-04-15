<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\GraphQl\Query\IdentityResolverInterface;

/**
 * Pool of IdentityResolverInterface objects
 */
class IdentityResolverPool
{
    /**
     * @var IdentityResolverInterface[]
     */
    private $identityResolvers = [];

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
     * Get an identity resolver by class name
     *
     * @param string $identityResolverClass
     * @return IdentityResolverInterface
     */
    public function get(string $identityResolverClass): IdentityResolverInterface
    {
        if (!isset($this->identityResolvers[$identityResolverClass])) {
            $this->identityResolvers[$identityResolverClass] = $this->objectManager->create($identityResolverClass);
        }
        return $this->identityResolvers[$identityResolverClass];
    }
}
