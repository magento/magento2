<?php
/**
 * Consumer builder factory.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Integration\Model\Oauth\Consumer;

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
     * Create consumer model.
     *
     * @param array $data
     * @return \Magento\Integration\Model\Oauth\Consumer
     */
    public function create(array $data = [])
    {
        $consumer = $this->_objectManager->create('Magento\Integration\Model\Oauth\Consumer', []);
        $consumer->setData($data);
        return $consumer;
    }
}
