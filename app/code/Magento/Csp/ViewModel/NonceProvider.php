<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\ViewModel;

use Magento\Csp\Helper\CspNonceProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * This class provides a nonce for the Content Security Policy (CSP) header.
 */
class NonceProvider implements ArgumentInterface
{
    /**
     * @var CspNonceProvider
     */
    private $cspNonceProvider;

    /**
     * @param CspNonceProvider $cspNonceProvider
     */
    public function __construct(
        CspNonceProvider $cspNonceProvider,
    ) {
        $this->cspNonceProvider = $cspNonceProvider;
    }

    /**
     * Returns a nonce for the Content Security Policy (CSP) header.
     *
     * @return string
     * @throws LocalizedException
     */
    public function getNonce(): string
    {
        return $this->cspNonceProvider->generateNonce();
    }
}
