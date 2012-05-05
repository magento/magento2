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
 * @package     Mage_XmlConnect
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group integrity
 */
class Integrity_Mage_XmlConnect_SkinFilesTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that skin files are available at both backend and frontend
     *
     * @param string $file
     * @dataProvider sharedSkinFilesDataProvider
     */
    public function testSharedSkinFiles($file)
    {
        $params = array(
            '_area'    => 'adminhtml',
            '_package' => 'default',
            '_theme'   => 'default',
        );
        $this->assertFileExists(Mage::getDesign()->getSkinFile($file, $params));
        $params['_area'] = 'frontend';
        $this->assertFileExists(Mage::getDesign()->getSkinFile($file, $params));
    }

    /**
     * @return array
     */
    public function sharedSkinFilesDataProvider()
    {
        return array(
            array('Mage_XmlConnect::images/tab_home.png'),
            array('Mage_XmlConnect::images/tab_shop.png'),
            array('Mage_XmlConnect::images/tab_search.png'),
            array('Mage_XmlConnect::images/tab_cart.png'),
            array('Mage_XmlConnect::images/tab_more.png'),
            array('Mage_XmlConnect::images/tab_account.png'),
            array('Mage_XmlConnect::images/tab_page.png'),
        );
    }
}
