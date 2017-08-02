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
 * @since 2.0.0
 */
class ScopeResolver implements \Magento\Framework\Url\ScopeResolverInterface
{
    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     * @since 2.0.0
     */
    protected $scopeResolver;

    /**
     * @var null|string
     * @since 2.0.0
     */
    protected $areaCode;

    /**
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param string|null $areaCode
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\ScopeResolverInterface $scopeResolver, $areaCode = null)
    {
        $this->scopeResolver = $scopeResolver;
        $this->areaCode = $areaCode;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getScopes()
    {
        return $this->scopeResolver->getScopes();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAreaCode()
    {
        return $this->areaCode;
    }
}
