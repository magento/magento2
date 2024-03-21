<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework;

use Magento\Framework\App\ScopeInterface;

interface ScopeSwitcherInterface
{
    /**
     * Set the current scope to the specified scope
     *
     * @param ScopeInterface $scope
     * @return ScopeInterface previous scope
     */
    public function switch(ScopeInterface $scope): ScopeInterface;
}
