<?php
/**
 * Application response factory
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App;

class ResponseFactory
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
     * Create response
     *
     * @param array $arguments
     * @return ResponseInterface
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Magento\Framework\App\ResponseInterface', $arguments);
    }
}
