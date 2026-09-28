<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Asset\File;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for @see \Magento\Framework\View\Asset\File\Context
 */
class ContextFactory
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
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\View\Asset\File\Context
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(Context::class, $data);
    }
}
