<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale\Bundle;

class DataBundle
{
    /**
     * @var string
     */
    protected $path = 'ICUDATA';

    /**
     * @var \ResourceBundle[][]
     */
    protected static $bundles = [];

    /**
     * Get resource bundle for the locale
     *
     * @param string $locale
     * @return \ResourceBundle
     */
    public function get($locale)
    {
        $class = get_class($this);
        if (!isset(static::$bundles[$class][$locale])) {
            $bundle = new \ResourceBundle($locale, $this->path);
            if (!$bundle && $this->path != 'ICUDATA') {
                $bundle = new \ResourceBundle($locale, 'ICUDATA');
            }
            static::$bundles[$class][$locale] = $bundle;
        }
        return static::$bundles[$class][$locale];
    }
}
