<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Scope;

use Magento\Framework\Exception\LocalizedException;

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
