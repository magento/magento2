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
 * @category    Magento
 * @package     Mage_Install
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Install_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    public function testCleanVarFolder()
    {
        $rootFolder = Mage::getConfig()->getVarDir() . DIRECTORY_SEPARATOR . 'parentFolder';
        $subFolderA = $rootFolder . DIRECTORY_SEPARATOR . 'subFolderA' . DIRECTORY_SEPARATOR;
        $subFolderB = $rootFolder . DIRECTORY_SEPARATOR . 'subFolderB' . DIRECTORY_SEPARATOR;
        @mkdir($subFolderA, 0777, true);
        @mkdir($subFolderB, 0777, true);
        @file_put_contents($subFolderB . 'test.txt', 'Some text here');
        $helper = Mage::helper('Mage_Install_Helper_Data');
        $helper->setVarSubFolders(array($rootFolder));
        $helper->cleanVarFolder();
        $this->assertFalse(is_dir($rootFolder));
    }
}
