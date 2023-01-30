<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import;

use Magento\Framework\Filesystem\Directory\Write;

/**
 * Import adapter model
 */
class Adapter
{
    /**
     * Adapter factory. Checks for availability, loads and create instance of import adapter object.
     *
     * @param string $type Adapter type ('csv', 'xml' etc.)
     * @param Write $directory
     * @param string $source
     * @param mixed $options OPTIONAL Adapter constructor options
     *
     * @return AbstractSource
     * phpcs:disable Magento2.Functions.StaticFunction
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public static function factory($type, $directory, $source, $options = null)
    {
        if (!is_string($type) || !$type) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The adapter type must be a non-empty string.')
            );
        }
        $adapterClass = 'Magento\ImportExport\Model\Import\Source\\' . ucfirst(strtolower($type));

        if (!class_exists($adapterClass)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('\'%1\' file extension is not supported', $type)
            );
        }
        $adapter = new $adapterClass($source, $directory, $options);

        if (!$adapter instanceof AbstractSource) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Adapter must be an instance of \Magento\ImportExport\Model\Import\AbstractSource')
            );
        }
        return $adapter;
    }

    /**
     * Create adapter instance for specified source file.
     *
     * @param string $source Source file path.
     * @param Write $directory
     * @param mixed $options OPTIONAL Adapter constructor options
     * phpcs:disable Magento2.Functions.StaticFunction
     * @return AbstractSource
     */
    public static function findAdapterFor($source, $directory, $options = null)
    {
        return self::factory(pathinfo($source, PATHINFO_EXTENSION), $directory, $source, $options); // phpcs:ignore
    }
}
