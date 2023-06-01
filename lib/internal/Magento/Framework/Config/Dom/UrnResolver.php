<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Resolve URN path to a real schema path
 */
namespace Magento\Framework\Config\Dom;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Phrase;

/**
 * @api
 * @since 100.0.2
 */
class UrnResolver
{
    /**
     * Get real file path by it's URN reference
     *
     * @param string $schema
     * @return string
     * @throws NotFoundException
     */
    public function getRealPath($schema)
    {
        if ($schema && strpos($schema, 'urn:') !== 0) {
            return $schema;
        }

        $componentRegistrar = new ComponentRegistrar();
        $matches = [];
        $modulePattern = '/urn:(?<vendor>([a-zA-Z]*)):module:(?<module>([A-Za-z0-9\_]*)):(?<path>(.+))/';
        $frameworkPattern = '/urn:(?<vendor>([a-zA-Z]*)):(?<framework>(framework[A-Za-z\-]*)):(?<path>(.+))/';
        $setupPattern = '/urn:(?<vendor>([a-zA-Z]*)):(?<setup>(setup[A-Za-z\-]*)):(?<path>(.+))/';
        if (preg_match($modulePattern, $schema, $matches)) {
            //urn:magento:module:Magento_Catalog:etc/catalog_attributes.xsd
            $package = $componentRegistrar
                ->getPath(ComponentRegistrar::MODULE, $matches['module']);
        } elseif (preg_match($frameworkPattern, $schema, $matches)) {
            //urn:magento:framework:Module/etc/module.xsd
            //urn:magento:framework-amqp:Module/etc/module.xsd
            $package = $componentRegistrar
                ->getPath(ComponentRegistrar::LIBRARY, $matches['vendor'] . '/' . $matches['framework']);
        } elseif (preg_match($setupPattern, $schema, $matches)) {
            //urn:magento:setup:
            $package = $componentRegistrar
                ->getPath(ComponentRegistrar::SETUP, $matches['vendor'] . '/' . $matches['setup']);
        } else {
            throw new NotFoundException(new Phrase("Unsupported format of schema location: '%1'", [$schema]));
        }
        $schemaPath = $package . '/' . $matches['path'];
        if (empty($package) || !file_exists($schemaPath)) {
            throw new NotFoundException(
                new Phrase("Could not locate schema: '%1' at '%2'", [$schema, $schemaPath])
            );
        }
        return $schemaPath;
    }

    /**
     * Callback registered for libxml to resolve URN to the file path
     *
     * @param string $public
     * @param string $system
     * @param array $context
     * @return resource
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function registerEntityLoader($public, $system, $context)
    {
        if ($system && strpos($system, 'urn:') === 0) {
            $filePath = $this->getRealPath($system);
        } else {
            if (file_exists($system)) {
                $filePath = $system;
            } else {
                throw new LocalizedException(new Phrase("File '%system' cannot be found", ['system' => $system]));
            }
        }
        return fopen($filePath, "r");
    }
}
