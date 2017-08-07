<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Scope;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface Validator for validating scope and scope code
 * @since 2.2.0
 */
interface ValidatorInterface
{
    /**
     * Validate if exists given scope and scope code
     * otherwise, throws an exception with appropriate message.
     *
     * @param string $scope
     * @param string $scopeCode
     * @return boolean
     * @throws LocalizedException
     * @since 2.2.0
     */
    public function isValid($scope, $scopeCode = null);
}
