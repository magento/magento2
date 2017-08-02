<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale\Bundle;

/**
 * Class \Magento\Framework\Locale\Bundle\DataBundle
 *
 * @since 2.0.0
 */
class DataBundle
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $path = 'ICUDATA';

    /**
     * @var \ResourceBundle[][]
     * @since 2.0.0
     */
    protected static $bundles = [];

    /**
     * Get resource bundle for the locale
     *
     * @param string $locale
     * @return \ResourceBundle
     * @since 2.0.0
     */
    public function get($locale)
    {
        $locale = $this->cleanLocale($locale);
        $class = get_class($this);
        if (!isset(static::$bundles[$class][$locale])) {
            $bundle = $this->createResourceBundle($locale, $this->path);
            if (!$bundle && $this->path != 'ICUDATA') {
                $bundle = $this->createResourceBundle($locale, 'ICUDATA');
            }
            static::$bundles[$class][$locale] = $bundle;
        }
        return static::$bundles[$class][$locale];
    }

    /**
     * @param string $locale
     * @param string $path
     * @return null|\ResourceBundle
     * @since 2.0.0
     */
    protected function createResourceBundle($locale, $path)
    {
        try {
            $bundle = new \ResourceBundle($locale, $path);
        } catch (\Exception $e) {
            // HHVM compatibility: constructor throws on invalid resource
            $bundle = null;
        }
        return $bundle;
    }

    /**
     * Clean locale leaving only language and script
     *
     * @param string $locale
     * @return string
     * @since 2.0.0
     */
    protected function cleanLocale($locale)
    {
        $localeParts = \Locale::parseLocale($locale);
        $cleanLocaleParts = [];
        if (isset($localeParts['language'])) {
            $cleanLocaleParts['language'] = $localeParts['language'];
        }
        if (isset($localeParts['script'])) {
            $cleanLocaleParts['script'] = $localeParts['script'];
        }
        return \Locale::composeLocale($cleanLocaleParts);
    }
}
