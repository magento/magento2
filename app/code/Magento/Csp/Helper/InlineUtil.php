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
    private $eventHandlersEnabled = false;

    /**
     * @param DynamicCollector $dynamicCollector
     */
    public function __construct(DynamicCollector $dynamicCollector)
    {
        $this->dynamicCollector = $dynamicCollector;
    }

    /**
     * Generate fetch policy hash for some content.
     *
     * @param string $content
     * @return string
     */
    private function generateHash(string $content): string
    {
        return 'sha256-' .base64_encode(hash('sha256', $content, true));
    }

    /**
     * @inheritDoc
     */
    public function renderTag(string $tagName, array $attributes, ?string $content = null): string
    {
        $remote = !empty($attributes['src'])
            ? $attributes['src'] : (!empty($attributes['href']) ? $attributes['href'] : null);
        if (!$remote && !$content) {
            throw new \InvalidArgumentException('Either remote URL or hashable content is required to whitelist');
        }
        switch ($tagName) {
            case 'script':
                $policyId = 'script-src';
                break;
            case 'style':
                $policyId = 'style-src';
                break;
            case 'img':
                $policyId = 'img-src';
                break;
            case 'audio':
            case 'video':
            case 'track':
                $policyId = 'media-src';
                break;
            case 'object':
            case 'embed':
            case 'applet':
                $policyId = 'object-src';
                break;
            case 'link':
                if (empty($attributes['rel']) || $attributes['rel'] !== 'stylesheet') {
                    throw new \InvalidArgumentException('Only remote styles can be whitelisted via "link" tag');
                }
                $policyId = 'style-src';
                break;
            default:
                throw new \InvalidArgumentException('Unknown source type - ' .$tagName);
        }

        if ($remote) {
            $urlData = parse_url($remote);
            $this->dynamicCollector->add(
                new FetchPolicy($policyId, false, [$urlData['scheme'] .'://' .$urlData['host']])
            );
        } elseif ($policyId === 'style-src' || $policyId === 'script-src') {
            $this->dynamicCollector->add(
                new FetchPolicy($policyId, false, [], [], false, false, false, [], [$this->generateHash($content)])
            );
        } else {
            throw new \InvalidArgumentException('Only inline scripts and styles can be whitelisted');
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
        if (!$this->eventHandlersEnabled) {
            $this->dynamicCollector->add(
                new FetchPolicy('default-src', false, [], [], false, false, false, [], [], false, true)
            );
            $this->eventHandlersEnabled = true;
        }

        $this->dynamicCollector->add(
            new FetchPolicy('script-src', false, [], [], false, false, false, [], [$this->generateHash($javascript)])
        );

        return $eventName .'="' .$javascript .'"';
    }
}
