<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Template;

use Magento\Framework\Exception\LocalizedException;

/**
 * Builds the placeholders that temporarily reserve the position of a directive result.
 *
 * Directive results are not written back into the template one by one, because an earlier
 * result could then be re-scanned as part of a later one. Every result takes a placeholder
 * built from the runtime signature instead, and all placeholders are resolved at once.
 */
class ResultPlaceholderFactory
{
    private const string SLOT_INFIX = ':slot';
    private const string SLOT_SUFFIX = ':';

    /**
     * @var SignatureProvider
     */
    private SignatureProvider $signatureProvider;

    /**
     * Constructor
     *
     * @param SignatureProvider $signatureProvider
     */
    public function __construct(SignatureProvider $signatureProvider)
    {
        $this->signatureProvider = $signatureProvider;
    }

    /**
     * Builds the placeholder reserving the given slot.
     *
     * @param int $slot
     * @return string
     * @throws LocalizedException
     */
    public function create(int $slot): string
    {
        return $this->signatureProvider->get() . self::SLOT_INFIX . $slot . self::SLOT_SUFFIX;
    }
}
