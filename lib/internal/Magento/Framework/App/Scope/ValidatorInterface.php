<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Scope;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface Validator for validating scope and scope code
 *
 * @api
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
     */
    public function isValid($scope, $scopeCode = null);
}
