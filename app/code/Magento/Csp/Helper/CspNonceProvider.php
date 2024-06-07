<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Csp\Helper;

use Magento\Csp\Model\Collector\DynamicCollector;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;

/**
 * This helper class is used to provide nonce for CSP
 *
 * It also adds a nonce to the CSP header.
 */
class CspNonceProvider
{
    /**
     * @var string
     */
    private const NONCE_LENGTH = 32;

    /**
     * @var string
     */
    private string $nonce;

    /**
     * @var Random
     */
    private Random $random;

    /**
     * @var DynamicCollector
     */
    private DynamicCollector $dynamicCollector;

    /**
     * @param Random $random
     * @param DynamicCollector $dynamicCollector
     */
    public function __construct(
        Random $random,
        DynamicCollector $dynamicCollector
    ) {
        $this->random = $random;
        $this->dynamicCollector = $dynamicCollector;
    }

    /**
     * Generate nonce and add it to the CSP header
     *
     * @return string
     * @throws LocalizedException
     */
    public function generateNonce(): string
    {
        if (empty($this->nonce)) {
            $this->nonce = $this->random->getRandomString(
                self::NONCE_LENGTH,
                Random::CHARS_DIGITS . Random::CHARS_LOWERS
            );

            $policy = new FetchPolicy(
                'script-src',
                false,
                [],
                [],
                false,
                false,
                false,
                [$this->nonce],
                []
            );

            $this->dynamicCollector->add($policy);
        }

        return base64_encode($this->nonce);
    }
}
