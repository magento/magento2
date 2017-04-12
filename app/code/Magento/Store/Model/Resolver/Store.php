<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Resolver;

class Store implements \Magento\Framework\App\ScopeResolverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\State\InitException
     */
    public function getScope($scopeId = null)
    {
        $scope = $this->_storeManager->getStore($scopeId);
        if (!$scope instanceof \Magento\Framework\App\ScopeInterface) {
            throw new \Magento\Framework\Exception\State\InitException(__('Invalid scope object'));
        }

        return $scope;
    }

    /**
     * Retrieve a list of available stores
     *
     * @return \Magento\Store\Model\Store[]
     */
    public function getScopes()
    {
        return $this->_storeManager->getStores();
    }
}
