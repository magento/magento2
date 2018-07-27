<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

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
