<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\Cookie;

use Magento\Framework\Stdlib\CookieDisablerInterface;

/**
 * Disables sending the cookies that are currently set.
 */
class PhpCookieDisabler implements CookieDisablerInterface
{
    /**
     * @inheritDoc
     */
    public function setCookiesDisabled(bool $disabled) : void
    {
        if ($disabled && !headers_sent()) {
            header_remove('Set-Cookie');
        }
    }
}
