<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * Class ScopeResolver
 *
 * URL scope resolver.
 */
class ScopeResolver implements \Magento\Framework\Url\ScopeResolverInterface
{
    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var null|string
     */
    protected $areaCode;

    /**
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param string|null $areaCode
     */
    public function __construct(\Magento\Framework\App\ScopeResolverInterface $scopeResolver, $areaCode = null)
    {
        $this->scopeResolver = $scopeResolver;
        $this->areaCode = $areaCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope($scopeId = null)
    {
        $scope = $this->scopeResolver->getScope($scopeId);
        if (!$scope instanceof \Magento\Framework\Url\ScopeInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Invalid scope object')
            );
        }

        return $scope;
    }

    /**
     * Retrieve array of URL scopes.
     *
     * @return \Magento\Framework\Url\ScopeInterface[]
     */
    public function getScopes()
    {
        return $this->scopeResolver->getScopes();
    }

    /**
     * {@inheritdoc}
     */
    public function getAreaCode()
    {
        return $this->areaCode;
    }
}
