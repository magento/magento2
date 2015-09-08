<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Resolve URN path to a real schema path
 */
namespace Magento\Framework\Config\Dom;

class UrnResolver
{
    /**
     * @var \Magento\Framework\Component\ModuleRegistrar
     */
    private static $moduleRegistrar;

    /**
     * @var \Magento\Framework\Component\LibraryRegistrar
     */
    private static $libraryRegistrar;

    /**
     * Get real file path by it's URN reference
     *
     * @param $schemaFileName
     * @return string
     */
    public static function getRealPath($schemaFileName)
    {
        if (substr($schemaFileName, 0, 4) == 'urn:') {
            if (!self::$moduleRegistrar) {
                self::$moduleRegistrar = new \Magento\Framework\Component\ModuleRegistrar();
            }
            if (!self::$libraryRegistrar) {
                self::$libraryRegistrar = new \Magento\Framework\Component\LibraryRegistrar();
            }
            // resolve schema location
            // urn:magento:module:catalog:etc/catalog_attributes.xsd
            // 0 : urn, 1: magento, 2: module, 3: catalog, 4: etc/catalog_attributes.xsd
            // BP/app/code/Magento/Catalog/etc/catalog_attributes.xsd
            // BP/vendor/magento/module-catalog/etc/catalog_attributes.xsd
            $urnParts = explode(':', $schemaFileName);
            if ($urnParts[2] == 'module') {
                $appModulePath = str_replace(' ', '', ucwords(str_replace('-', ' ', $urnParts[3])));
                $moduleName = ucfirst($urnParts[1]) . '_' . $appModulePath;
                $appSchemaPath = self::$moduleRegistrar->getPath($moduleName) . '/' . $urnParts[4];
            } else if ($urnParts[2] == 'library') {
                // urn:magento:library:framework:Module/etc/module.xsd
                // 0: urn, 1: magento, 2:library, 3: framework, 4: Module/etc/module.xsd
                $moduleName = $urnParts[1] . '/' . $urnParts[3];
                $appSchemaPath = self::$libraryRegistrar->getPath($moduleName) . '/' . $urnParts[4];
            } else {
                throw new \UnexpectedValueException("Unsupported format of schema location: " . $schemaFileName);
            }
            if (!empty($appSchemaPath) && file_exists($appSchemaPath)) {
                $schemaFileName = $appSchemaPath;
            } else {
                throw new \UnexpectedValueException("Could not locate schema: " . $schemaFileName);
            }
        }
        return $schemaFileName;
    }
}
