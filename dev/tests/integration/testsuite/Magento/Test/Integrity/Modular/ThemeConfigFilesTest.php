<?php
/**
 * Tests that existing page_layouts.xml files are valid to schema individually and merged.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

use Magento\Framework\App\Filesystem\DirectoryList;

class ThemeConfigFilesTest extends \Magento\TestFramework\TestCase\AbstractConfigFiles
{
    /**
     * Returns directory (modules, library internal stc.) constant which contains XSD file
     *
     * @return string
     */
    protected function getDirectoryConstant()
    {
        return DirectoryList::LIB_INTERNAL;
    }

    /**
     * Returns the reader class name that will be instantiated via ObjectManager
     *
     * @return string reader class name
     */
    protected function _getReaderClassName()
    {
        return 'Magento\Theme\Model\Layout\Config\Reader';
    }

    /**
     * Returns a string that represents the path to the config file, starting in the app directory.
     *
     * Format is glob, so * is allowed.
     *
     * @return string
     */
    protected function _getConfigFilePathGlob()
    {
        return '/*/*/view/*/layouts.xml';
    }

    /**
     * Returns a path to the per file XSD file, relative to the library directory.
     *
     * @return string
     */
    protected function _getXsdPath()
    {
        return '/Magento/Framework/View/PageLayout/etc/layouts.xsd';
    }
}
