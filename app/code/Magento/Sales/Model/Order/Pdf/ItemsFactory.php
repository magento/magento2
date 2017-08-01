<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Pdf;

/**
 * Factory class for \Magento\Sales\Model\Order\Pdf\Items\AbstractItems
 *
 * @internal
 * @since 2.0.0
 */
class ItemsFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $instanceName
     * @param array $data
     * @return \Magento\Sales\Model\Order\Pdf\Items\AbstractItems
     * @since 2.0.0
     */
    public function get($instanceName, array $data = [])
    {
        return $this->_objectManager->get($instanceName, $data);
    }
}
