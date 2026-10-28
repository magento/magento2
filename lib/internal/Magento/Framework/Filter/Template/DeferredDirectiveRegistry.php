<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Template;

use Magento\Framework\Math\Random;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Per-request registry of directives that child templates have deferred to the root template.
 *
 * The service is shared for the duration of a request so that a child template (filtered by its
 * own Filter instance) and the root template can exchange deferrals through it.
 */
class DeferredDirectiveRegistry implements ResetAfterRequestInterface
{
    /**
     * Length of the per-request salt.
     */
    private const TOKEN_LENGTH = 32;

    /**
     * @var Random
     */
    private Random $random;

    /**
     * Map of placeholder token => deferred directive text.
     *
     * @var string[]
     */
    private array $directives = [];

    /**
     * Per-request secret salt used to derive placeholder tokens deterministically.
     *
     * @var string|null
     */
    private ?string $salt = null;

    /**
     * @param Random $random
     */
    public function __construct(Random $random)
    {
        $this->random = $random;
    }

    /**
     * Register a directive as deferred and return the placeholder token that represents it.
     *
     * @param string $directive
     * @return string
     * @throws LocalizedException
     */
    public function defer(string $directive): string
    {
        if ($this->salt === null) {
            $this->salt = $this->random->getRandomString(self::TOKEN_LENGTH);
        }

        $placeholder = ':deferred:' . hash('sha256', $this->salt . $directive) . ':';

        $this->directives[$placeholder] = $directive;

        return $placeholder;
    }

    /**
     * Get all registered deferrals keyed by their placeholder token.
     *
     * @return string[]
     */
    public function getAll(): array
    {
        return $this->directives;
    }

    /**
     * Forget all registered deferrals.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->directives = [];
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->directives = [];
        $this->salt = null;
    }
}
