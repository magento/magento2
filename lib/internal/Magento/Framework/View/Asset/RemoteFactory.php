<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for \Magento\Framework\View\Asset\Remote
 * @since 2.0.0
 */
class RemoteFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\View\Asset\Remote
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(Remote::class, $data);
    }
}
