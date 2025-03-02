<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address;

/**
 * Address composite validator.
 */
class CompositeSanitizer implements SanitizerInterface
{
    /**
     * @var SanitizerInterface[]
     */
    private array $sanitizers;

    /**
     * @param array $sanitizers
     */
    public function __construct(
        array $sanitizers = []
    )
    {
        $this->sanitizers = $sanitizers;
    }

    /**
     * @inheritdoc
     */
    public function sanitize(AbstractAddress $address): AbstractAddress
    {
        foreach ($this->sanitizers as $sanitizer) {
            $sanitizer->sanitize($address);
        }

        return $address;
    }
}
