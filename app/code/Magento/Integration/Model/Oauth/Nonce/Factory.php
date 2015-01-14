<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Oauth\Nonce;

/**
 * Nonce builder factory.
 */
class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create nonce model.
     *
     * @param array $arguments
     * @return \Magento\Integration\Model\Oauth\Nonce
     */
    public function create($arguments = [])
    {
        return $this->_objectManager->create('Magento\Integration\Model\Oauth\Nonce', $arguments);
    }
}
