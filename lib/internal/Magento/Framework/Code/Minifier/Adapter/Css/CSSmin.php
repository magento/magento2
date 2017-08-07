<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Minifier\Adapter\Css;

use tubalmartin\CssMin\Minifier as CssMinLibrary;
use Magento\Framework\Code\Minifier\AdapterInterface;

/**
 * Adapter for CSSmin library
 */
class CSSmin implements AdapterInterface
{
    /**
     * 'pcre.recursion_limit' value for CSSMin minification
     */
    const PCRE_RECURSION_LIMIT = 1000;

    /**
     * @var CssMinLibrary
     */
    protected $cssMinifier;

    /**
     * @param CssMinLibrary $cssMinifier
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(CssMinLibrary $cssMinifier)
    {
        // TODO: set $cssMinifier in constructor once MAGETWO-51176 is resolved.
    }

    /**
     * Get CSS Minifier
     *
     * @return CssMinLibrary
     * @since 2.1.0
     */
    private function getCssMin()
    {
        if (!($this->cssMinifier instanceof CssMinLibrary)) {
            $this->cssMinifier = new CssMinLibrary(false);
        }
        return $this->cssMinifier;
    }

    /**
     * Minify css file content
     *
     * @param string $content
     * @return string
     */
    public function minify($content)
    {
        $pcreRecursionLimit = ini_get('pcre.recursion_limit');
        ini_set('pcre.recursion_limit', self::PCRE_RECURSION_LIMIT);
        $result = $this->getCssMin()->run($content);
        ini_set('pcre.recursion_limit', $pcreRecursionLimit);
        return $result;
    }
}
