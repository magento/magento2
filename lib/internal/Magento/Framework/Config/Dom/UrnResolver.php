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
use Magento\Framework\Module\PackageInfo;

class UrnResolver
{
    /**
     * Component registrar
     *
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * Package info
     *
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * Constructor
     *
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param PackageInfo $packageInfo
     */
    public function __construct(ComponentRegistrarInterface $componentRegistrar, PackageInfo $packageInfo)
    {
        $this->componentRegistrar = $componentRegistrar;
        $this->packageInfo = $packageInfo;
    }

    /**
     * Get real file path by it's URN reference
     *
     * @param string $schema
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getRealPath($schema)
    {
        if (substr($schema, 0, 4) == 'urn:') {
            // resolve schema location
            // urn:magento:module:catalog:etc/catalog_attributes.xsd
            // 0 : urn, 1: magento, 2: module, 3: catalog, 4: etc/catalog_attributes.xsd
            // moduleName -> Magento_Catalog
            $urnParts = explode(':', $schema);
            if ($urnParts[2] == 'module') {
                $moduleName = $this->packageInfo->getModuleName($urnParts[1] . '/' . $urnParts[2] . '-' . $urnParts[3]);
                $schemaPath = $this->componentRegistrar->getPath(
                        ComponentRegistrar::MODULE, $moduleName
                    ) . '/' . $urnParts[4];
            } else if ($urnParts[2] == 'library') {
                // urn:magento:library:framework:Module/etc/module.xsd
                // 0: urn, 1: magento, 2:library, 3: framework, 4: Module/etc/module.xsd
                // libaryName -> magento/framework
                $libraryName = $urnParts[1] . '/' . $urnParts[3];
                $schemaPath = $this->componentRegistrar->getPath(
                        ComponentRegistrar::LIBRARY, $libraryName
                    ) . '/' . $urnParts[4];
            } else {
                throw new \UnexpectedValueException("Unsupported format of schema location: " . $schema);
            }
            if (!empty($schemaPath) && file_exists($schemaPath)) {
                $schema = $schemaPath;
            } else {
                throw new \UnexpectedValueException(
                    "Could not locate schema: '" . $schema . "' at '" . $schemaPath . "'");
            }
        }
        return $schema;
    }
}
