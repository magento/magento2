<?php

namespace Magento\SearchStorefront\Model\Scope;

class ScopeResolver implements \Magento\Framework\App\ScopeResolverInterface
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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function getScope($scopeId = null)
    {
        $scope = $this->_storeManager->getStore($scopeId);

        if (!$scope instanceof \Magento\Framework\App\ScopeInterface) {
            throw new \Magento\Framework\Exception\State\InitException(
                __('The scope object is invalid. Verify the scope object and try again.')
            );
        }

        return $scope;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\State\InitException
     */
    public function getScopes()
    {
        return $this->_storeManager->getStores();
    }
}
