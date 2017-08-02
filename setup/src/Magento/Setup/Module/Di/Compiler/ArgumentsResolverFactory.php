<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler;

/**
 * Class \Magento\Setup\Module\Di\Compiler\ArgumentsResolverFactory
 *
 * @since 2.0.0
 */
class ArgumentsResolverFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @return \Magento\Setup\Module\Di\Compiler\ArgumentsResolver
     * @since 2.0.0
     */
    public function create(\Magento\Framework\ObjectManager\ConfigInterface $diContainerConfig)
    {
        return new ArgumentsResolver($diContainerConfig);
    }
}
