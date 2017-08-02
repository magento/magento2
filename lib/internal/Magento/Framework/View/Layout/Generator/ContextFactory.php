<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Generator;

/**
 * Class \Magento\Framework\View\Layout\Generator\ContextFactory
 *
 */
class ContextFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\View\Layout\Generator\Context
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(\Magento\Framework\View\Layout\Generator\Context::class, $data);
    }
}
