<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Resolver;

use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Exception\State\InitException;
use Magento\Store\Model\Store as ModelStore;
use Magento\Store\Model\StoreManagerInterface;

class Store implements ScopeResolverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @throws InitException
     */
    public function getScope($scopeId = null)
    {
        $scope = $this->_storeManager->getStore($scopeId);
        if (!$scope instanceof ScopeInterface) {
            throw new InitException(
                __('The scope object is invalid. Verify the scope object and try again.')
            );
        }

        return $scope;
    }

    /**
     * Retrieve a list of available stores
     *
     * @return ModelStore[]
     */
    public function getScopes()
    {
        return $this->_storeManager->getStores();
    }
}
