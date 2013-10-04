<?php
/**
 * Token builder factory.
 *
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Oauth\Model\Token;

class Factory
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create token model.
     *
     * @param array $arguments
     * @return \Magento\Oauth\Model\Token
     */
    public function create($arguments = array())
    {
        return $this->_objectManager->create('Magento\Oauth\Model\Token', $arguments);
    }
}
