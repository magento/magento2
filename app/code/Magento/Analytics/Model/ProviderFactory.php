<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ProviderFactory
 *
 * Factory for report providers
 * @since 2.2.0
 */
class ProviderFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.2.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $providerName
     * @return object
     * @since 2.2.0
     */
    public function create($providerName)
    {
        return $this->objectManager->get($providerName);
    }
}
