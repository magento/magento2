<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Resolver;

use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Exception\State\InitException;
use Magento\Store\Model\Group as ModelGroup;
use Magento\Store\Model\StoreManagerInterface;

class Group implements ScopeResolverInterface
{
    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        protected readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * {@inheritdoc}
     * @throws InitException
     */
    public function getScope($scopeId = null)
    {
        $scope = $this->storeManager->getGroup($scopeId);
        if (!$scope instanceof ScopeInterface) {
            throw new InitException(__('The scope object is invalid. Verify the scope object and try again.'));
        }

        return $scope;
    }

    /**
     * Retrieve a list of available groups
     *
     * @return ModelGroup[]
     */
    public function getScopes()
    {
        return $this->storeManager->getGroups();
    }
}
