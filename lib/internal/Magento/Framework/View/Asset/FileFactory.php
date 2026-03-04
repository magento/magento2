<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Asset;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for \Magento\Framework\View\Asset\File
 *
 * @api
 */
class FileFactory
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
     * @return \Magento\Framework\View\Asset\File
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(File::class, $data);
    }
}
