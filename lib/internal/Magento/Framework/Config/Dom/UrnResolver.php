<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Resolve URN path to a real schema path
 */
namespace Magento\Framework\Config\Dom;

use Magento\Framework\Component\ComponentRegistrar;

class UrnResolver
{
    /**
     * Get real file path by it's URN reference
     *
     * @param string $schema
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getRealPath($schema)
    {
        $componentRegistrar = new ComponentRegistrar();
        if (substr($schema, 0, 4) == 'urn:') {
            // resolve schema location
            $urnParts = explode(':', $schema);
            if ($urnParts[2] == 'module') {
                // urn:magento:module:Magento_Catalog:etc/catalog_attributes.xsd
                // 0 : urn, 1: magento, 2: module, 3: Magento_Catalog, 4: etc/catalog_attributes.xsd
                // moduleName -> Magento_Catalog
                $schemaPath = $componentRegistrar->getPath(
                    ComponentRegistrar::MODULE,
                    $urnParts[3]
                ) . '/' . $urnParts[4];
            } else if (strpos($urnParts[2], 'framework') === 0) {
                // urn:magento:framework:Module/etc/module.xsd
                // 0: urn, 1: magento, 2: framework, 3: Module/etc/module.xsd
                // libaryName -> magento/framework
                $libraryName = $urnParts[1] . '/' . $urnParts[2];
                $schemaPath = $componentRegistrar->getPath(
                    ComponentRegistrar::LIBRARY,
                    $libraryName
                ) . '/' . $urnParts[3];
            } else {
                throw new \UnexpectedValueException("Unsupported format of schema location: " . $schema);
            }
            if (!empty($schemaPath) && file_exists($schemaPath)) {
                $schema = $schemaPath;
            } else {
                throw new \UnexpectedValueException(
                    "Could not locate schema: '" . $schema . "' at '" . $schemaPath . "'"
                );
            }
        }
        return $schema;
    }
}
