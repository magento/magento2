<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Interface \Magento\Framework\App\ScopeFallbackResolverInterface
 *
 * @since 2.1.0
 */
interface ScopeFallbackResolverInterface
{
    /**
     * Return Scope and Scope ID of parent scope
     *
     * @param string $scope
     * @param int|null $scopeId
     * @param bool $forConfig
     * @return array [scope, scopeId]
     * @since 2.1.0
     */
    public function getFallbackScope($scope, $scopeId, $forConfig = true);
}
