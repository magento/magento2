<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Resolver;

class Website implements \Magento\Framework\App\ScopeResolverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\State\InitException
     */
    public function getScope($scopeId = null)
    {
        $scope = $this->_storeManager->getWebsite($scopeId);
        if (!($scope instanceof \Magento\Framework\App\ScopeInterface)) {
            throw new \Magento\Framework\Exception\State\InitException(__('Invalid scope object'));
        }

        return $scope;
    }

    /**
     * Retrieve a list of available websites
     *
     * @return \Magento\Store\Model\Website[]
     */
    public function getScopes()
    {
        return $this->_storeManager->getWebsites();
    }
}
