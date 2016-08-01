<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Grid;

use Magento\Framework\Composer\ComposerInformation;

/**
 * Class TypeMapper
 */
class TypeMapper
{
    /**#@+
     * Constants for package types in setup grid
     */
    const UNDEFINED_PACKAGE_TYPE = 'Undefined';
    const EXTENSION_PACKAGE_TYPE = 'Extension';
    /**#@-*/
    
    /**
     * @var ComposerInformation
     */
    private $composerInformation;

    /**
     * @var array
     */
    private $rootRequires;

    /** @var array */
    private $packageTypesMap = [
        ComposerInformation::THEME_PACKAGE_TYPE => 'Theme',
        ComposerInformation::LANGUAGE_PACKAGE_TYPE => 'Language',
        ComposerInformation::MODULE_PACKAGE_TYPE => 'Module',
        ComposerInformation::METAPACKAGE_PACKAGE_TYPE => 'Metapackage',
        ComposerInformation::COMPONENT_PACKAGE_TYPE => 'Component',
        ComposerInformation::LIBRARY_PACKAGE_TYPE => 'Library'
    ];

    /**
     * TypeMapper constructor.
     * @param ComposerInformation $composerInformation
     */
    public function __construct(
        ComposerInformation $composerInformation
    ) {
        $this->composerInformation = $composerInformation;
    }

    /**
     * Retrieve package type for a grid.
     *
     * @param string $packageName
     * @param string $packageType
     * @return string
     */
    public function map($packageName, $packageType)
    {
        if (in_array($packageName, $this->getRootRequires())
            && $packageType == ComposerInformation::MODULE_PACKAGE_TYPE
        ) {
            return self::EXTENSION_PACKAGE_TYPE;
        }

        return isset($this->packageTypesMap[$packageType]) ?
            $this->packageTypesMap[$packageType] : self::UNDEFINED_PACKAGE_TYPE;
    }

    /**
     * @return array
     */
    private function getRootRequires()
    {
        if ($this->rootRequires === null) {
            $this->rootRequires = array_keys($this->composerInformation->getRootPackage()->getRequires());
        }
        return $this->rootRequires;
    }
}
