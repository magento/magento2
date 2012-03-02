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
 * @group module:Mage_Xmlconnect
 */
class Mage_XmlConnect_Helper_ImageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $application
     * @param string $file
     * @dataProvider getSkinImagesUrlDataProvider
     */
    public function testGetSkinImagesUrl($application, $file)
    {
        $helper = new Mage_XmlConnect_Helper_Image;
        Mage::getDesign()->setDesignTheme('default/default/default', $application);

        $this->assertStringMatchesFormat(
            "http://%s/media/skin/{$application}/%s/%s/%s/%s/Mage_XmlConnect/images/{$file}",
            $helper->getSkinImagesUrl($file)
        );
        $this->assertFileExists(Mage::getDesign()->getSkinFile("Mage_XmlConnect::/images/{$file}"));
    }

    /**
     * @return array
     */
    public function getSkinImagesUrlDataProvider()
    {
        return array(
            array('adminhtml', 'dropdown-arrow.gif'),
            array('adminhtml', 'design_default/accordion_open.png'),
            array('adminhtml', 'mobile_preview/1.gif'),
            array('frontend', 'tab_cart.png'),
        );
    }
}
