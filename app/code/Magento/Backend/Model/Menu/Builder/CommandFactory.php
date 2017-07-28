<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu\Builder;

/**
 * Menu builder command factory
 * @api
 * @since 2.0.0
 */
class CommandFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new command object
     *
     * @param string $commandName
     * @param array $data
     * @return \Magento\Config\Model\Config
     * @since 2.0.0
     */
    public function create($commandName, array $data = [])
    {
        return $this->_objectManager->create(
            'Magento\Backend\Model\Menu\Builder\Command\\' . ucfirst($commandName),
            $data
        );
    }
}
