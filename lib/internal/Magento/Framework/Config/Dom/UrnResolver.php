<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Resolve URN path to a real schema path
 */
namespace Magento\Framework\Config\Dom;

use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Component\ComponentRegistrar;

class UrnResolver
{
    /**
     * Component registrar
     *
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * Constructor
     *
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(ComponentRegistrarInterface $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Get real file path by it's URN reference
     *
     * @param $schemaFileName
     * @return string
     */
    public function getRealPath($schemaFileName)
    {
        if (substr($schemaFileName, 0, 4) == 'urn:') {
            // resolve schema location
            // urn:magento:module:catalog:etc/catalog_attributes.xsd
            // 0 : urn, 1: magento, 2: module, 3: catalog, 4: etc/catalog_attributes.xsd
            // moduleName -> Magento_Catalog
            $urnParts = explode(':', $schemaFileName);
            if ($urnParts[2] == 'module') {
                $appModulePath = str_replace(' ', '', ucwords(str_replace('-', ' ', $urnParts[3])));
                $moduleName = ucfirst($urnParts[1]) . '_' . $appModulePath;
                $appSchemaPath = $this->componentRegistrar->getPath(
                        ComponentRegistrar::MODULE, $moduleName
                    ) . '/' . $urnParts[4];
            } else if ($urnParts[2] == 'library') {
                // urn:magento:library:framework:Module/etc/module.xsd
                // 0: urn, 1: magento, 2:library, 3: framework, 4: Module/etc/module.xsd
                // libaryName -> magento/framework
                $libraryName = $urnParts[1] . '/' . $urnParts[3];
                $appSchemaPath = $this->componentRegistrar->getPath(
                        ComponentRegistrar::LIBRARY, $libraryName
                    ) . '/' . $urnParts[4];
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
