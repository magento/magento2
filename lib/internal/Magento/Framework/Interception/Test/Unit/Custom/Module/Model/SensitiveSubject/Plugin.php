<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\SensitiveSubject;

use Magento\Framework\Interception\InterceptorInterface;
use RuntimeException;

/**
 * Around plugin for exceptions from a plugin or the intercepted method.
 */
class Plugin
{
    /**
     * Throw from the plugin or continue to the original method.
     */
    public function aroundAuthenticate(
        InterceptorInterface $subject,
        callable $proceed,
        #[\SensitiveParameter] string $password,
        string $label
    ): string {
        if ($label === 'plugin') {
            throw new RuntimeException($subject::class);
        }
        return $proceed($password, $label);
    }
}
