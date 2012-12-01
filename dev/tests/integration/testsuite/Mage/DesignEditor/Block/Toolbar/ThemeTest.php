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
 * @package     Mage_DesignEditor
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for theme block functioning
 */
class Mage_DesignEditor_Block_Toolbar_ThemeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_DesignEditor_Block_Toolbar_Theme
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = Mage::app()->getLayout()->createBlock('Mage_DesignEditor_Block_Toolbar_Theme');
    }

    protected function tearDown()
    {
        $this->_block = null;
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testIsThemeSelected()
    {
        $themeOld = Mage::getObjectManager()->create('Mage_Core_Model_Theme')
            ->setData($this->_getThemeSampleData())
            ->setThemePath('a/b')
            ->setThemeCode('b')
            ->save();

        $themeNew = Mage::getObjectManager()->create('Mage_Core_Model_Theme')
            ->setData($this->_getThemeSampleData())
            ->setThemePath('c/d')
            ->setThemeCode('d')
            ->save();

        Mage::getDesign()->setDesignTheme($themeOld);
        $isSelected = $this->_block->isThemeSelected($themeOld->getId());
        $this->assertTrue($isSelected);

        Mage::getDesign()->setDesignTheme($themeNew);
        $isSelected = $this->_block->isThemeSelected($themeOld->getId());
        $this->assertFalse($isSelected);
    }

    public function testGetSelectHtmlId()
    {
        $value = $this->_block->getSelectHtmlId();
        $this->assertNotEmpty($value);
    }

    /**
     * @return array
     */
    protected function _getThemeSampleData()
    {
        return array(
            'theme_title'          => 'Default',
            'theme_version'        => '2.0.0.0',
            'parent_theme'         => null,
            'is_featured'          => true,
            'magento_version_from' => '2.0.0.0-dev1',
            'magento_version_to'   => '*',
            'preview_image'        => '',
            'area'                 => 'frontend',
            'theme_directory'      => implode(
                DIRECTORY_SEPARATOR, array(__DIR__, '_files', 'design', 'frontend', 'default', 'default')
            )
        );
    }
}
