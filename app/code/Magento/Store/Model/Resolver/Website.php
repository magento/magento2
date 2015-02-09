<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Resolver;

class Website implements \Magento\Framework\App\ScopeResolverInterface
{
    /**
     * @var \Magento\Framework\Store\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Store\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Store\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\App\InitException
     */
    public function getScope($scopeId = null)
    {
        $scope = $this->_storeManager->getWebsite($scopeId);
        if (!($scope instanceof \Magento\Framework\App\ScopeInterface)) {
            throw new \Magento\Framework\App\InitException('Invalid scope object');
        }

        return $scope;
    }
}
