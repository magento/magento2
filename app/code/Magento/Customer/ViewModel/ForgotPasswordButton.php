<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Forgot password button view model
 */
class ForgotPasswordButton implements ArgumentInterface
{
    /**
     * If Forgot password button should be disabled
     *
     * @return bool
     */
    public function disabled(): bool
    {
        return false;
    }
}
