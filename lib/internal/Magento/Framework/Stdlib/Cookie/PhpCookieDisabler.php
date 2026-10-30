<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
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
