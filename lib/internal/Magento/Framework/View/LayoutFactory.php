<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

/**
 * Factory class for Layout
 */
class LayoutFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Framework\View\LayoutInterface::class
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return LayoutInterface
     * @throws \InvalidArgumentException
     */
    public function create(array $data = [])
    {
        $layout = $this->_objectManager->create($this->_instanceName, $data);
        if (!$layout instanceof LayoutInterface) {
            throw new \InvalidArgumentException(get_class($layout) . ' must be an instance of LayoutInterface.');
        }
        return $layout;
    }
}
