<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Policy;

/**
 * Represents a fetch directive.
 */
class FetchPolicy implements SimplePolicyInterface
{
    /**
     * List of possible fetch directives.
     */
    public const POLICIES = [
        'default-src',
        'child-src',
        'connect-src',
        'font-src',
        'frame-src',
        'img-src',
        'manifest-src',
        'media-src',
        'object-src',
        'script-src',
        'style-src',
        'base-uri',
        'form-action',
        'frame-ancestors'
    ];

    /**
     * @var string
     */
    private $id;

    /**
     * @var string[]
     */
    private $hostSources;

    /**
     * @var string[]
     */
    private $schemeSources;

    /**
     * @var bool
     */
    private $selfAllowed;

    /**
     * @var bool
     */
    private $inlineAllowed;

    /**
     * @var bool
     */
    private $evalAllowed;

    /**
     * @var bool
     */
    private $noneAllowed;

    /**
     * @var string[]
     */
    private $nonceValues;

    /**
     * @var string[]
     */
    private $hashes;

    /**
     * @var bool
     */
    private $dynamicAllowed;

    /**
     * @var bool
     */
    private $eventHandlersAllowed;

    /**
     * @param string $id
     * @param bool $noneAllowed
     * @param string[] $hostSources
     * @param string[] $schemeSources
     * @param bool $selfAllowed
     * @param bool $inlineAllowed
     * @param bool $evalAllowed
     * @param string[] $nonceValues
     * @param string[] $hashValues
     * @param bool $dynamicAllowed
     * @param bool $eventHandlersAllowed
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $id,
        bool $noneAllowed = true,
        array $hostSources = [],
        array $schemeSources = [],
        bool $selfAllowed = false,
        bool $inlineAllowed = false,
        bool $evalAllowed = false,
        array $nonceValues = [],
        array $hashValues = [],
        bool $dynamicAllowed = false,
        bool $eventHandlersAllowed = false
    ) {
        $this->id = $id;
        $this->noneAllowed = $noneAllowed;
        $this->hostSources = array_unique($hostSources);
        $this->schemeSources = array_unique($schemeSources);
        $this->selfAllowed = $selfAllowed;
        $this->inlineAllowed = $inlineAllowed;
        $this->evalAllowed = $evalAllowed;
        $this->nonceValues = array_unique($nonceValues);
        $this->hashes = $hashValues;
        $this->dynamicAllowed = $dynamicAllowed;
        $this->eventHandlersAllowed = $eventHandlersAllowed;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Items can be loaded from given hosts.
     *
     * @return string[]
     */
    public function getHostSources(): array
    {
        return $this->hostSources;
    }

    /**
     * Items can be loaded using following schemes.
     *
     * @return string[]
     */
    public function getSchemeSources(): array
    {
        return $this->schemeSources;
    }

    /**
     * Items can be loaded from the same host/port as the HTML page.
     *
     * @return bool
     */
    public function isSelfAllowed(): bool
    {
        return $this->selfAllowed;
    }

    /**
     * Items can be loaded from tags present on the original HTML page.
     *
     * @return bool
     */
    public function isInlineAllowed(): bool
    {
        return $this->inlineAllowed;
    }

    /**
     * Allows creating items from strings.
     *
     * For example using "eval()" for JavaScript.
     *
     * @return bool
     */
    public function isEvalAllowed(): bool
    {
        return $this->evalAllowed;
    }

    /**
     * Content type governed by this policy is disabled completely.
     *
     * @return bool
     */
    public function isNoneAllowed(): bool
    {
        return $this->noneAllowed;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getValue(): string
    {
        if ($this->isNoneAllowed()) {
            return '\'none\'';
        } else {
            $sources = $this->getHostSources();
            foreach ($this->getSchemeSources() as $schemeSource) {
                $sources[] = $schemeSource .':';
            }
            if ($this->isSelfAllowed()) {
                $sources[] = '\'self\'';
            }
            if ($this->isInlineAllowed()) {
                $sources[] = '\'unsafe-inline\'';
            }
            if ($this->isEvalAllowed()) {
                $sources[] = '\'unsafe-eval\'';
            }
            if ($this->isDynamicAllowed()) {
                $sources[] = '\'strict-dynamic\'';
            }
            if ($this->areEventHandlersAllowed()) {
                $sources[] = '\'unsafe-hashes\'';
            }
            if (!$this->isInlineAllowed()) {
                foreach ($this->getNonceValues() as $nonce) {
                    $sources[] = '\'nonce-' . base64_encode($nonce) . '\'';
                }
                foreach ($this->getHashes() as $hash => $algorithm) {
                    $sources[] = "'$algorithm-$hash'";
                }
            }

            return implode(' ', $sources);
        }
    }

    /**
     * Unique cryptographically random numbers marking inline items as trusted.
     *
     * Contains only numbers, not encoded.
     *
     * @return string[]
     */
    public function getNonceValues(): array
    {
        return $this->nonceValues;
    }

    /**
     * Unique hashes generated based on inline items marking them as trusted.
     *
     * Contains only hashes themselves, encoded into base64. Keys are the hashes, values are algorithms used.
     *
     * @return string[]
     */
    public function getHashes(): array
    {
        return $this->hashes;
    }

    /**
     * Is trust to inline items propagated to items loaded by root items.
     *
     * @return bool
     */
    public function isDynamicAllowed(): bool
    {
        return $this->dynamicAllowed;
    }

    /**
     * Allows to whitelist event handlers (but not javascript: URLs) with hashes.
     *
     * @return bool
     */
    public function areEventHandlersAllowed(): bool
    {
        return $this->eventHandlersAllowed;
    }
}
