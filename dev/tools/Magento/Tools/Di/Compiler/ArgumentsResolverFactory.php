<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\Di\Compiler;

class ArgumentsResolverFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with config
     *
     * @param \Magento\Framework\ObjectManager\ConfigInterface $diContainerConfig
     * @return \Magento\Tools\Di\Compiler\ArgumentsResolver
     */
    public function create(\Magento\Framework\ObjectManager\ConfigInterface $diContainerConfig)
    {
        return new ArgumentsResolver($diContainerConfig);
    }
}
