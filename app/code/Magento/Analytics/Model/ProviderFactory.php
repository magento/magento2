<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for report providers
 */
class ProviderFactory
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
     * Object creation
     *
     * @param string $providerName
     * @return object
     */
    public function create($providerName)
    {
        return $this->objectManager->get($providerName);
    }
}
