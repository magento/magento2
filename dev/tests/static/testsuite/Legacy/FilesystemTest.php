<?php
/**
 * Backwards-incompatible changes in file system
 *
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
 * @category    tests
 * @package     static
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Legacy_FilesystemTest extends PHPUnit_Framework_TestCase
{
    /**
     * Directories may re-appear again during merging, therefore ensure they were properly relocated
     *
     * @param string $path
     * @dataProvider relocationsDataProvider
     */
    public function testRelocations($path)
    {
        $this->assertFileNotExists(Utility_Files::init()->getPathToSource() . DIRECTORY_SEPARATOR . $path);
    }

    public function relocationsDataProvider()
    {
        return array(
            'Relocated to pub/errors' => array('errors'),
            'Eliminated with Mage_Compiler' => array('includes'),
            'Relocated to pub/js' => array('js'),
            'Relocated to pub/media' => array('media'),
            'Eliminated as not needed' => array('pkginfo'),
            'Dissolved into themes under app/design ' => array('skin'),
            'Dissolved into different modules\' view/frontend' => array('app/design/frontend/base'),
            'Dissolved into different modules\' view/email/*.html' => array('app/locale/en_US/template'),
        );
    }

    public function testObsoleteDirectories()
    {
        $area    = '*';
        $package = '*';
        $theme   = '*';
        $root = Utility_Files::init()->getPathToSource();
        $dirs = glob("{$root}/app/design/{$area}/{$package}/{$theme}/template", GLOB_ONLYDIR);
        $msg = array();
        if ($dirs) {
            $msg[] = 'Theme "template" directories are obsolete. Relocate files as follows:';
            foreach ($dirs as $dir) {
                $msg[] = str_replace($root, '',
                    "{$dir} => " . realpath($dir . '/..') . '/Namespace_Module/*'
                );
            }
        }

        $dirs = glob("{$root}/app/design/{$area}/{$package}/{$theme}/layout", GLOB_ONLYDIR);
        if ($dirs) {
            $msg[] = 'Theme "layout" directories are obsolete. Relocate layout files into the root of theme directory.';
            $msg = array_merge($msg, $dirs);
        }

        if ($msg) {
            $this->fail(implode(PHP_EOL, $msg));
        }
    }
}
