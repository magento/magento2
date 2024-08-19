<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Helper;

use Magento\Csp\Model\Collector\DynamicCollector;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\Math\Random;

/**
 * This mock class is used to test nonce for CSP
 */
class CspNonceProviderMock extends CspNonceProvider
{
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
        parent::__construct($random, $dynamicCollector);

        $this->random = $random;
        $this->dynamicCollector = $dynamicCollector;
    }

    /**
     * Generate nonce and add it to the CSP header
     *
     * @return string
     */
    public function generateNonce(): string
    {
        $cspNonce = 'nonce-1234567890abcdef';

        $policy = new FetchPolicy(
            'script-src',
            false,
            [],
            [],
            false,
            false,
            false,
            [$cspNonce],
            []
        );

        $this->dynamicCollector->add($policy);

        return $cspNonce;
    }
}
