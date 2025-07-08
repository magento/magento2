<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Minifier\Adapter\Js;

use MatthiasMullie\Minify;
use Magento\Framework\Code\Minifier\AdapterInterface;

/**
 * Adapter for JShrink library
 */
class JShrink implements AdapterInterface
{
    /**
     * Takes a string containing JavaScript and removes unneeded characters
     * to shrink the code without altering its functionality.
     *
     * @param string $content The raw JavaScript to be minified
     * @throws \Exception
     * @return bool|string
     */
    public function minify($content): bool|string
    {
        $minifier = new Minify\JS();

        return $minifier->add($content)->minify();
    }
}
