<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Minifier\Adapter\Js;

use JShrink\Minifier;
use Magento\Framework\Code\Minifier\AdapterInterface;

/**
 * Adapter for JShrink library
 */
class JShrink implements AdapterInterface
{
    /**
     * Takes a string containing javascript and removes unneeded characters in
     * order to shrink the code without altering it's functionality.
     *
     * @param string $content The raw javascript to be minified
     * @throws \Exception
     * @return bool|string
     */
    public function minify($content)
    {
        return Minifier::minify($content);
    }
}
