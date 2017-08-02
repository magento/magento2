<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Resolver;

use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Exception\State\InitException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class \Magento\Store\Model\Resolver\Group
 *
 * @since 2.1.0
 */
class Group implements ScopeResolverInterface
{
    /**
     * @var StoreManagerInterface
     * @since 2.1.0
     */
    protected $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @since 2.1.0
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @throws InitException
     * @since 2.1.0
     */
    public function getScope($scopeId = null)
    {
        $scope = $this->storeManager->getGroup($scopeId);
        if (!$scope instanceof ScopeInterface) {
            throw new InitException(__('Invalid scope object'));
        }

        return $scope;
    }

    /**
     * Retrieve a list of available groups
     *
     * @return \Magento\Store\Model\Group[]
     * @since 2.1.0
     */
    public function getScopes()
    {
        return $this->storeManager->getGroups();
    }
}
