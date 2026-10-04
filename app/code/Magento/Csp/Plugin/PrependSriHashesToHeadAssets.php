<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config\Renderer;

/**
 * Ensures window.sriHashes is defined before requirejs-config.js executes.
 *
 * csp.sri.hashes is a child of head.additional in layout XML, so it normally
 * renders after all head assets. On a cached load requirejs-config.js executes
 * instantly and fires sri.js onNodeCreated before head.additional has parsed,
 * leaving window.sriHashes undefined.
 *
 * This plugin fixes the ordering by:
 * 1. Pulling csp.sri.hashes out of head.additional's children list.
 * 2. Calling toHtml() on it to produce the window.sriHashes inline script.
 * 3. Prepending that HTML to the assets output so it is defined first on the page.
 */
class PrependSriHashesToHeadAssets
{
    /**
     * Block name for the SRI hashes inline script.
     *
     * @var string
     */
    private const HASHES_BLOCK_NAME = 'csp.sri.hashes';

    /**
     * @param LayoutInterface $layout
     */
    public function __construct(
        private readonly LayoutInterface $layout
    ) {
    }

    /**
     * Prepend window.sriHashes inline script before head assets.
     *
     * Fetches csp.sri.hashes as a child of head.additional so the check is
     * naturally idempotent: once unsetChild() runs the child lookup returns
     * null, preventing a double-prepend if renderHeadAssets() is called again.
     *
     * @param Renderer $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRenderHeadAssets(Renderer $subject, string $result): string
    {
        $headAdditional = $this->layout->getBlock('head.additional');
        if (!$headAdditional) {
            return $result;
        }

        $hashesBlock = $headAdditional->getChildBlock(self::HASHES_BLOCK_NAME);
        if (!$hashesBlock) {
            return $result;
        }

        // Child confirmed above — remove it so head.additional does not render it a second time.
        $headAdditional->unsetChild(self::HASHES_BLOCK_NAME);

        return $hashesBlock->toHtml() . $result;
    }
}
