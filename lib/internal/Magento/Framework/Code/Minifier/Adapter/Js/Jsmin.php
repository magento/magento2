<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Minifier\Adapter\Js;

if (!class_exists('JSMin')) {
    require_once __DIR__ . '/../../../../../../JSMin/jsmin.php';
}
/**
 * Adapter for JSMin library
 */
class Jsmin implements \Magento\Framework\Code\Minifier\AdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function minify($content)
    {
        return \JSMin::minify($content);
    }
}
