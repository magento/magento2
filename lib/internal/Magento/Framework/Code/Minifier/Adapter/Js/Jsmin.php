<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
