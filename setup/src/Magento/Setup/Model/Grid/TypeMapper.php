<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Grid;

use Magento\Framework\Composer\ComposerInformation;

/**
 * Class TypeMapper
 * @since 2.2.0
 */
class TypeMapper
{
    /**#@+
     * Constants for package types in setup grid
     */
    const UNDEFINED_PACKAGE_TYPE = 'Undefined';
    const EXTENSION_PACKAGE_TYPE = 'Extension';
    const THEME_PACKAGE_TYPE = 'Theme';
    const MODULE_PACKAGE_TYPE = 'Module';
    const LANGUAGE_PACKAGE_TYPE = 'Language';
    const METAPACKAGE_PACKAGE_TYPE = 'Metapackage';
    const COMPONENT_PACKAGE_TYPE = 'Component';
    const LIBRARY_PACKAGE_TYPE = 'Library';
    /**#@-*/

    /** @var array */
    private $packageTypesMap = [
        ComposerInformation::THEME_PACKAGE_TYPE => self::THEME_PACKAGE_TYPE,
        ComposerInformation::LANGUAGE_PACKAGE_TYPE => self::LANGUAGE_PACKAGE_TYPE,
        ComposerInformation::MODULE_PACKAGE_TYPE => self::MODULE_PACKAGE_TYPE,
        ComposerInformation::METAPACKAGE_PACKAGE_TYPE => self::METAPACKAGE_PACKAGE_TYPE,
        ComposerInformation::COMPONENT_PACKAGE_TYPE => self::COMPONENT_PACKAGE_TYPE,
        ComposerInformation::LIBRARY_PACKAGE_TYPE => self::LIBRARY_PACKAGE_TYPE
    ];

    /**
     * Retrieve package type for a grid.
     *
     * @param string $packageType
     * @return string
     * @internal param string $packageName
     * @since 2.2.0
     */
    public function map($packageType)
    {
        return isset($this->packageTypesMap[$packageType]) ?
            $this->packageTypesMap[$packageType] : self::UNDEFINED_PACKAGE_TYPE;
    }
}
