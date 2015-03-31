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
        return $this->cssMinifier->run($content);
    }
}
