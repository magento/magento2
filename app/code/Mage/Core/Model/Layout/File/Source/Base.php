<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Source of base layout files introduced by modules
 */
class Mage_Core_Model_Layout_File_Source_Base implements Mage_Core_Model_Layout_File_SourceInterface
{
    /**
     * @var Magento_Filesystem
     */
    private $_filesystem;

    /**
     * @var Mage_Core_Model_Dir
     */
    private $_dirs;

    /**
     * @var Mage_Core_Model_Layout_File_Factory
     */
    private $_fileFactory;

    /**
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_Dir $dirs
     * @param Mage_Core_Model_Layout_File_Factory $fileFactory
     */
    public function __construct(
        Magento_Filesystem $filesystem,
        Mage_Core_Model_Dir $dirs,
        Mage_Core_Model_Layout_File_Factory $fileFactory
    ) {
        $this->_filesystem = $filesystem;
        $this->_dirs = $dirs;
        $this->_fileFactory = $fileFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getFiles(Mage_Core_Model_ThemeInterface $theme)
    {
        $namespace = $module = '*';
        $area = $theme->getArea();
        $files = $this->_filesystem->searchKeys(
            $this->_dirs->getDir(Mage_Core_Model_Dir::MODULES),
            "{$namespace}/{$module}/view/{$area}/layout/*.xml"
        );
        $result = array();
        foreach ($files as $filename) {
            $moduleDir = dirname(dirname(dirname(dirname($filename))));
            $module = basename($moduleDir);
            $namespace = basename(dirname($moduleDir));
            $moduleFull = "{$namespace}_{$module}";
            $result[] = $this->_fileFactory->create($filename, $moduleFull);
        }
        return $result;
    }
}
