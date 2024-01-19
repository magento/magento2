<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib;

/**
 * This interface is for when you need to disable all cookies from being sent in the HTTP response
 */
interface CookieDisablerInterface
{
    public function setCookiesDisabled(bool $disabled) : void;
}
