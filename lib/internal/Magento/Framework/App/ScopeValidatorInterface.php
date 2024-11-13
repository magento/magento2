<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

/**
 * Interface \Magento\Framework\App\ScopeValidatorInterface
 *
 * @api
 */
interface ScopeValidatorInterface
{
    /**
     * Check that scope and scope id is exists
     *
     * @param string $scope
     * @param string $scopeId
     * @return bool
     */
    public function isValidScope($scope, $scopeId = null);
}
