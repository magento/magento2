<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        if (strpos($schema, 'urn:') !== 0) {
            return $schema;
        }

        $componentRegistrar = new ComponentRegistrar();
        $matches = [];
        $modulePattern = '/urn:(?<vendor>([a-zA-Z]*)):module:(?<module>([A-Za-z\_]*)):(?<path>(.+))/';
        $frameworkPattern = '/urn:(?<vendor>([a-zA-Z]*)):(?<framework>(framework[A-Za-z\-]*)):(?<path>(.+))/';
        if (preg_match($modulePattern, $schema, $matches)) {
            //urn:magento:module:Magento_Catalog:etc/catalog_attributes.xsd
            $package = $componentRegistrar
                ->getPath(ComponentRegistrar::MODULE, $matches['module']);
        } else if (preg_match($frameworkPattern, $schema, $matches)) {
            //urn:magento:framework:Module/etc/module.xsd
            //urn:magento:framework-amqp:Module/etc/module.xsd
            $package = $componentRegistrar
                ->getPath(ComponentRegistrar::LIBRARY, $matches['vendor'] . '/' . $matches['framework']);
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
        if (strpos($system, 'urn:') === 0) {
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
