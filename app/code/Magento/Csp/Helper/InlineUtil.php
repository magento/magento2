<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Helper;

use Magento\Csp\Api\InlineUtilInterface;
use Magento\Csp\Model\Collector\DynamicCollector;
use Magento\Csp\Model\Policy\FetchPolicy;
use ParagonIE\Sodium\Core\Curve25519\Fe;

/**
 * Helper for classes responsible for rendering and templates.
 *
 * Allows to whitelist dynamic sources specific to a certain page.
 */
class InlineUtil implements InlineUtilInterface
{
    /**
     * @var DynamicCollector
     */
    private $dynamicCollector;

    /**
     * @var bool
     */
    private $useUnsafeHashes;

    private static $tagMeta = [
        'script' => ['id' => 'script-src', 'remote' => ['src'], 'hash' => true],
        'style' => ['id' => 'style-src', 'remote' => [], 'hash' => true],
        'img' => ['id' => 'img-src', 'remote' => ['src']],
        'audio' => ['id' => 'media-src', 'remote' => ['src']],
        'video' => ['id' => 'media-src', 'remote' => ['src']],
        'track' => ['id' => 'media-src', 'remote' => ['src']],
        'source' => ['id' => 'media-src', 'remote' => ['src']],
        'object' => ['id' => 'object-src', 'remote' => ['data', 'archive']],
        'embed' => ['id' => 'object-src', 'remote' => ['src']],
        'applet' => ['id' => 'object-src', 'remote' => ['code', 'archive']],
        'link' => ['id' => 'style-src', 'remote' => ['href']],
        'form' => ['id' => 'form-action', 'remote' => ['action']],
        'iframe' => ['id' => 'frame-src', 'remote' => ['src']],
        'frame' => ['id' => 'frame-src', 'remote' => ['src']]
    ];

    /**
     * @param DynamicCollector $dynamicCollector
     * @param bool $useUnsafeHashes Use 'unsafe-hashes' policy (not supported by CSP v2).
     */
    public function __construct(DynamicCollector $dynamicCollector, bool $useUnsafeHashes = false)
    {
        $this->dynamicCollector = $dynamicCollector;
        $this->useUnsafeHashes = $useUnsafeHashes;
    }

    /**
     * Generate fetch policy hash for some content.
     *
     * @param string $content
     * @return array Hash data to insert into a FetchPolicy.
     */
    private function generateHashValue(string $content): array
    {
        return [base64_encode(hash('sha256', $content, true)) => 'sha256'];
    }

    /**
     * Extract host for a fetch policy from a URL.
     *
     * @param string $url
     * @return string|null Null is returned when URL does not point to a remote host.
     */
    private function extractHost(string $url): ?string
    {
        $urlData = parse_url($url);
        if (
            !$urlData
            || empty($urlData['scheme'])
            || ($urlData['scheme'] !== 'http' && $urlData['scheme'] !== 'https')
        ) {
            return null;
        }

        return $urlData['scheme'] .'://' .$urlData['host'];
    }

    /**
     * Extract remote hosts used to get fonts.
     *
     * @param string $styleContent
     * @return string[]
     */
    private function extractRemoteFonts(string $styleContent): array
    {
        $urlsFound = [[]];
        preg_match_all('/\@font\-face\s*?\{([^\}]*)[^\}]*?\}/im', $styleContent, $fontFaces);
        foreach ($fontFaces[1] as $fontFaceContent) {
            preg_match_all('/url\((http(s)?\:[^\)]+)\)/i', $fontFaceContent, $urls);
            $urlsFound[] = $urls[1];
        }

        return array_map([$this, 'extractHost'], array_merge(...$urlsFound));
    }

    /**
     * @inheritDoc
     */
    public function renderTag(string $tagName, array $attributes, ?string $content = null): string
    {
        if (!array_key_exists($tagName, self::$tagMeta)) {
            throw new \InvalidArgumentException('Unknown source type - ' .$tagName);
        }
        /** @var string[] $remotes */
        $remotes = [];
        foreach (self::$tagMeta[$tagName]['remote'] as $remoteAttr) {
            if (!empty($attributes[$remoteAttr]) && $host = $this->extractHost($attributes[$remoteAttr])) {
                $remotes[] = $host;
                break;
            }
        }
        /** @var string $policyId */
        $policyId = self::$tagMeta[$tagName]['id'];
        if ($tagName === 'style' && $content) {
            $remotes += $this->extractRemoteFonts($content);
        }

        if (empty($remotes) && !$content) {
            throw new \InvalidArgumentException('Either remote URL or hashable content is required to whitelist');
        }

        if ($remotes) {
            $this->dynamicCollector->add(
                new FetchPolicy($policyId, false, $remotes)
            );
        }
        if ($content && !empty(self::$tagMeta[$tagName]['hash'])) {
            $this->dynamicCollector->add(
                new FetchPolicy($policyId, false, [], [], false, false, false, [], $this->generateHashValue($content))
            );
        }

        $html = '<' .$tagName;
        foreach ($attributes as $attribute => $value) {
            $html .= ' ' .$attribute .'="' .$value .'"';
        }
        if ($content) {
            $html .= '>' .$content .'</' .$tagName .'>';
        } else {
            $html .= ' />';
        }

        return $html;
    }

    /**
     * @inheritDoc
     */
    public function renderEventListener(string $eventName, string $javascript): string
    {
        if ($this->useUnsafeHashes) {
            $policy = new FetchPolicy(
                'script-src',
                false,
                [],
                [],
                false,
                false,
                false,
                [],
                $this->generateHashValue($javascript),
                false,
                true
            );
        } else {
            $policy = new FetchPolicy('script-src', false, [], [], false, true);
        }
        $this->dynamicCollector->add($policy);

        return $eventName .'="' .$javascript .'"';
    }
}
