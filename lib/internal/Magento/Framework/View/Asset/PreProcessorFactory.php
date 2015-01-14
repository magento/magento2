<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * View asset pre-processor factory
 */
class PreProcessorFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Object manager
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $instanceName
     * @param array $data
     * @return \Magento\Framework\View\Asset\PreProcessorInterface
     * @throws \UnexpectedValueException
     */
    public function create($instanceName, array $data = [])
    {
        $processorInstance = $this->objectManager->create($instanceName, $data);
        if (!$processorInstance instanceof \Magento\Framework\View\Asset\PreProcessorInterface) {
            throw new \UnexpectedValueException("{$instanceName} has to implement the pre-processor interface.");
        }
        return $processorInstance;
    }
}
