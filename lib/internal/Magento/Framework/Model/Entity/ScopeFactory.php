<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ScopeFactory
 * @since 2.1.0
 */
class ScopeFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    private $objectManager;

    /**
     * ScopeFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.1.0
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
     * @since 2.1.0
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
