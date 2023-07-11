<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\ViewModel\Customer;

use Magento\Customer\Model\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Customer's auth view model
 */
class Auth implements ArgumentInterface
{
    /**
     * @param HttpContext $httpContext
     */
    public function __construct(
        private HttpContext $httpContext
    ) {
    }

    /**
     * Check is user login
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH) ?? false;
    }
}
