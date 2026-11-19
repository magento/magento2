<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model;

/**
 * Subject that throws with sensitive arguments on the stack.
 */
class SensitiveSubject
{
    /**
     * Fail authentication for a nonempty password.
     */
    public function authenticate(#[\SensitiveParameter] string $password, string $label): string
    {
        if ($password !== '') {
            throw new \RuntimeException($label);
        }
        return $label;
    }
}
