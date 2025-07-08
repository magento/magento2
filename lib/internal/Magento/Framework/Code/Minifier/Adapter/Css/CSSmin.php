<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Minifier\Adapter\Css;

use Magento\Framework\Code\Minifier\AdapterInterface;
use MatthiasMullie\Minify\CSS;

/**
 * Adapter for CSSmin library
 */
class CSSmin implements AdapterInterface
{
    /**
     * Minify css file content
     *
     * @param string $content
     * @return string
     */
    public function minify($content): string
    {
        $cssMinifier = new CSS();

        return $cssMinifier->add($content)->minify();
    }
}
