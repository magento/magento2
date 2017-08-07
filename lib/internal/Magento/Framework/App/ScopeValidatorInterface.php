<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Interface \Magento\Framework\App\ScopeValidatorInterface
 *
 * @since 2.1.0
 */
interface ScopeValidatorInterface
{
    /**
     * Check that scope and scope id is exists
     *
     * @param string $scope
     * @param string $scopeId
     * @return bool
     * @since 2.1.0
     */
    public function isValidScope($scope, $scopeId = null);
}
