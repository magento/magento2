<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Resolver;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Pool of IdentityInterface objects
 */
class IdentityPool
{
    /**
     * @var IdentityInterface[]
     */
    private $identityInstances = [];

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
     * @param string $identityClass
     * @return IdentityInterface
     */
    public function get(string $identityClass): IdentityInterface
    {
        if (!isset($this->identityInstances[$identityClass])) {
            $this->identityInstances[$identityClass] = $this->objectManager->create($identityClass);
        }
        return $this->identityInstances[$identityClass];
    }
}
