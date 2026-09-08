<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Template;

use Magento\Framework\Exception\LocalizedException;

/**
 * Builds and validates the signature markers that delimit a deferred directive.
 *
 * A deferred directive is a directive that must be processed in scope of a parent
 * template instead of its own scope (e.g. {{inlinecss}}). Wrapping it in a pair of
 * markers built from the runtime signature lets the parent tell apart the directives
 * its own children deferred to it from directives injected through template data.
 */
class SignatureMarker
{
    private const string OPENING_SUFFIX = ':deferred:';
    private const string CLOSING_PREFIX = ':end:';
    private const array BRACKET_DELIMITERS = [
        '(' => ')',
        '{' => '}',
        '[' => ']',
        '<' => '>'
    ];

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
     * Returns the marker that opens a deferred directive.
     *
     * @return string
     * @throws LocalizedException
     */
    private function openingMarker(): string
    {
        return $this->signatureProvider->get() . self::OPENING_SUFFIX;
    }

    /**
     * Returns the marker that closes a deferred directive.
     *
     * @return string
     * @throws LocalizedException
     */
    private function closingMarker(): string
    {
        return self::CLOSING_PREFIX . $this->signatureProvider->get();
    }

    /**
     * Wraps the given content in the runtime marker pair.
     *
     * @param string $content
     * @return string
     * @throws LocalizedException
     */
    public function wrap(string $content): string
    {
        return $this->openingMarker() . $content . $this->closingMarker();
    }

    /**
     * Tells whether the given value carries the runtime signature anywhere within it.
     *
     * @param string $value
     * @return bool
     * @throws LocalizedException
     */
    public function containsSignature(string $value): bool
    {
        return str_contains($value, $this->signatureProvider->get());
    }

    /**
     * Checks that a construction is delimited by exactly one marker pair and carries no other signature.
     *
     * A construction whose body carries the signature was either forged or nested, and must not be
     * treated as a directive this template deferred to itself.
     *
     * @param string $construction
     * @return bool
     * @throws LocalizedException
     */
    public function hasSingleMarkerPair(string $construction): bool
    {
        $opening = $this->openingMarker();
        $closing = $this->closingMarker();

        if (!str_starts_with($construction, $opening) || !str_ends_with($construction, $closing)) {
            return false;
        }

        $body = substr($construction, strlen($opening), -strlen($closing));

        return !str_contains($body, $this->signatureProvider->get());
    }

    /**
     * Removes every marker left in the given value.
     *
     * @param string $value
     * @return string
     * @throws LocalizedException
     */
    public function stripMarkers(string $value): string
    {
        return str_replace([$this->openingMarker(), $this->closingMarker()], '', $value);
    }

    /**
     * Wraps the pattern body so it only matches a construction delimited by a marker pair.
     *
     * @param string $pattern
     * @return string
     * @throws LocalizedException
     */
    public function embedIntoPattern(string $pattern): string
    {
        $openingDelimiter = substr(trim($pattern), 0, 1);
        $closingDelimiter = self::BRACKET_DELIMITERS[$openingDelimiter] ?? $openingDelimiter;

        $pattern = substr_replace(
            $pattern,
            $this->openingMarker(),
            strpos($pattern, $openingDelimiter) + 1,
            0
        );

        return substr_replace($pattern, $this->closingMarker(), strrpos($pattern, $closingDelimiter), 0);
    }
}
