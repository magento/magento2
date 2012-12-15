<?php
/**
 * Nonce builder factory.
 *
 * @copyright Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 */
class Mage_Oauth_Model_Nonce_Factory
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create nonce model.
     *
     * @param array $arguments
     * @return Mage_Oauth_Model_Nonce
     */
    public function create($arguments = array())
    {
        return $this->_objectManager->create('Mage_Oauth_Model_Nonce', $arguments);
    }
}
