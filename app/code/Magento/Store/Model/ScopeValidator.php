<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use InvalidArgumentException;
use Magento\Framework\App\ScopeValidatorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\ScopeResolverPool;

class ScopeValidator implements ScopeValidatorInterface
{
    /**
     * @param ScopeResolverPool $scopeResolverPool
     */
    public function __construct(
        protected readonly ScopeResolverPool $scopeResolverPool
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isValidScope($scope, $scopeId = null)
    {
        if ($scope == ScopeConfigInterface::SCOPE_TYPE_DEFAULT && !$scopeId) {
            return true;
        }

        try {
            $scopeResolver = $this->scopeResolverPool->get($scope);
            if (!$scopeResolver->getScope($scopeId)->getId()) {
                return false;
            }
        } catch (InvalidArgumentException $e) {
            return false;
        } catch (NoSuchEntityException $e) {
            return false;
        }

        return true;
    }
}
