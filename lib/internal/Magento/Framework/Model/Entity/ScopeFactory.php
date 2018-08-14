<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ScopeFactory
 */
class ScopeFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * ScopeFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $identifier
     * @param string $value
     * @param ScopeInterface|null $fallback
     * @return ScopeInterface
     */
    public function create($identifier, $value, $fallback = null)
    {
        return $this->objectManager->create(
            ScopeInterface::class,
            [
                'identifier' => $identifier,
                'value' => $value,
                'fallback' => $fallback
            ]
        );
    }
}
