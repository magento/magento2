<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Template;

/**
 * Provider of a signature.
 *
 * Provides a signature which should be used to sign deferred directives
 * (directives that should be processed in scope of a parent template
 * instead of own scope, e.g. {{inlinecss}}).
 */
class SignatureProvider
{
    /**
     * @var string|null
     */
    private $signature;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $random;

    /**
     * @param \Magento\Framework\Math\Random $random
     */
    public function __construct(
        \Magento\Framework\Math\Random $random
    ) {
        $this->random = $random;
    }

    /**
     * Generates a random string which will be used as a signature during runtime.
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get(): string
    {
        if ($this->signature === null) {
            $this->signature = $this->random->getRandomString(32);
        }

        return $this->signature;
    }
}
