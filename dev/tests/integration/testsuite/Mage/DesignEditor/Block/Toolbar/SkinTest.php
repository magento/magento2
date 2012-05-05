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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for skin block functioning
 */
class Mage_DesignEditor_Block_Toolbar_SkinTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_DesignEditor_Block_Toolbar_Skin
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = new Mage_DesignEditor_Block_Toolbar_Skin();
    }

    public function testGetOptions()
    {
        Mage::getConfig()->getOptions()->setDesignDir(__DIR__ . '/../../../Core/Model/Design/Source/_files/design');
        $options = $this->_block->getOptions();

        $this->assertInternalType('array', $options);
        $this->assertNotEmpty($options);

        foreach ($options as $optGroup) {
            $this->assertInternalType('array', $optGroup);
            $this->assertArrayHasKey('label', $optGroup);
            $this->assertArrayHasKey('value', $optGroup);
            $this->assertInternalType('array', $optGroup['value']);
            foreach ($optGroup['value'] as $option) {
                $this->assertInternalType('array', $option);
                $this->assertArrayHasKey('label', $option);
                $this->assertArrayHasKey('value', $option);
                $this->assertInternalType('string', $option['label']);
                $this->assertInternalType('string', $option['value']);
            }
        }
    }

    public function  testIsSkinSelected()
    {
        $oldTheme = Mage::getDesign()->getDesignTheme();
        Mage::getDesign()->setDesignTheme('a/b/c');
        $isSelected = $this->_block->isSkinSelected('a/b/c');
        Mage::getDesign()->setDesignTheme($oldTheme);
        $this->assertTrue($isSelected);

        Mage::getDesign()->setDesignTheme('a/b/c');
        $isSelected = $this->_block->isSkinSelected('c/b/a');
        Mage::getDesign()->setDesignTheme($oldTheme);
        $this->assertFalse($isSelected);
    }

    public function testGetSelectHtmlId()
    {
        $value = $this->_block->getSelectHtmlId();
        $this->assertNotEmpty($value);
    }
}
