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
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Catalog_Model_Design.
 */
class Mage_Catalog_Model_DesignTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Design
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Mage::getModel('Mage_Catalog_Model_Design');
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * @dataProvider getThemeModel
     */
    public function testApplyCustomDesign($theme)
    {
        $this->_model->applyCustomDesign($theme);
        $this->assertEquals('package', Mage::getDesign()->getDesignTheme()->getPackageCode());
        $this->assertEquals('theme', Mage::getDesign()->getDesignTheme()->getThemeCode());
    }

    /**
     * @return Mage_Core_Model_Theme
     */
    public function getThemeModel()
    {
        $theme = Mage::getModel('Mage_Core_Model_Theme');
        $theme->setData($this->_getThemeData());
        return array(array($theme));
    }

    /**
     * @return array
     */
    protected function _getThemeData()
    {
        return array(
            'theme_title'          => 'Magento Theme',
            'theme_code'           => 'theme',
            'package_code'         => 'package',
            'theme_path'           => 'package/theme',
            'theme_version'        => '2.0.0.0',
            'parent_theme'         => null,
            'is_featured'          => true,
            'magento_version_from' => '2.0.0.0-dev1',
            'magento_version_to'   => '*',
            'preview_image'        => '',
            'theme_directory'      => implode(
                DIRECTORY_SEPARATOR, array(__DIR__, '_files', 'design', 'frontend', 'default', 'default')
            )
        );
    }
}
