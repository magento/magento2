<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Minifier\Adapter\Css;

use CSSmin;
use Magento\Framework\Code\Minifier\AdapterInterface;

class CssMinifier implements AdapterInterface
{
    /**
     * 'pcre.recursion_limit' value for CSSMin minification
     */
    const PCRE_RECURSION_LIMIT = 1000;

    /**
     * @var CSSmin
     */
    protected $cssMinifier;

    /**
     * @param CSSmin $cssMinifier
     */
    public function __construct(CSSmin $cssMinifier)
    {
        $this->cssMinifier = $cssMinifier;
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
        $result = $this->cssMinifier->run($content);
        ini_set('pcre.recursion_limit', $pcreRecursionLimit);
        return $result;
    }
}
