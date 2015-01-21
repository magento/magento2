<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

class ScopeResolver implements \Magento\Framework\Url\ScopeResolverInterface
{
    /**
     * @var \Magento\Framework\Store\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var null|string
     */
    protected $_areaCode;

    /**
     * @param \Magento\Framework\Store\StoreManagerInterface $storeManager
     * @param string|null $areaCode
     */
    public function __construct(\Magento\Framework\Store\StoreManagerInterface $storeManager, $areaCode = null)
    {
        $this->_storeManager = $storeManager;
        $this->_areaCode = $areaCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope($scopeId = null)
    {
        $scope = $this->_storeManager->getStore($scopeId);
        if (!$scope instanceof \Magento\Framework\Url\ScopeInterface) {
            throw new \Magento\Framework\Exception('Invalid scope object');
        }

        return $scope;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes()
    {
        return $this->_storeManager->getStores();
    }

    /**
     * {@inheritdoc}
     */
    public function getAreaCode()
    {
        return $this->_areaCode;
    }
}
