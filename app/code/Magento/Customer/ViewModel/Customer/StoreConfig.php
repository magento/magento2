<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\ViewModel\Customer;

use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class StoreConfig implements ArgumentInterface
{

    /**
     * @var ConfigShare
     */
    private ConfigShare $configShare;

    /**
     * Constructor
     *
     * @param ConfigShare $configShare
     */
    public function __construct(
        ConfigShare $configShare
    ) {
        $this->configShare = $configShare;
    }

    /**
     * Get global account sharing is enabled or not
     *
     * @return bool
     */
    public function isGlobalScopeEnabled(): bool
    {
        return $this->configShare->isGlobalScope();
    }
}
