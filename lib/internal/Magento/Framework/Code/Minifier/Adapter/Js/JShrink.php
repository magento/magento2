<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Minifier\Adapter\Js;

use JShrink\Minifier;
use Magento\Framework\Code\Minifier\AdapterInterface;

/**
 * Adapter for JShrink library
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function minify($content)
    {
        return Minifier::minify($content);
    }
}
