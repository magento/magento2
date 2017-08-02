<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Generator;

/**
 * Class \Magento\Framework\View\Layout\Generator\ContextFactory
 *
 * @since 2.0.0
 */
class ContextFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(\Magento\Framework\View\Layout\Generator\Context::class, $data);
    }
}
