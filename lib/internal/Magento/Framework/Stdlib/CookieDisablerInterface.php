<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib;

/**
 * This interface is for when you need to disable all cookies from being sent in the HTTP response
 */
interface CookieDisablerInterface
{
    /**
     * Set Cookies Disabled.  If true, cookies won't be sent.
     *
     * @param bool $disabled
     * @return void
     */
    public function setCookiesDisabled(bool $disabled) : void;
}
