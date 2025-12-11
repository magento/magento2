<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

/**
 * Interface \Magento\Framework\App\ScopeFallbackResolverInterface
 *
 * @api
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
     */
    public function getFallbackScope($scope, $scopeId, $forConfig = true);
}
