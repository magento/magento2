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
 * @package     Mage_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Weight_RendererTest extends PHPUnit_Framework_TestCase
{
    const VIRTUAL_FIELD_HTML_ID = 'weight_and_type_switcher';

    /**
     * @var Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Weight_Renderer
     */
    protected $_model;

    /**
     * @var Varien_Data_Form_Element_Checkbox
     */
    protected $_virtual;

    public function testSetForm()
    {
        $this->_virtual = new Varien_Object();

        $helper = $this->getMock('Mage_Catalog_Helper_Product', array('getTypeSwitcherControlLabel'),
            array(), '', false, false
        );
        $helper->expects($this->any())->method('getTypeSwitcherControlLabel')
            ->will($this->returnValue('Virtual / Downloadable'));

        $this->assertNull($this->_virtual->getId());
        $this->assertNull($this->_virtual->getName());
        $this->assertNull($this->_virtual->getLabel());
        $this->assertNull($this->_virtual->getForm());

        $this->_model = new Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Weight_Renderer(
            array('element' => $this->_virtual, 'helper' => $helper)
        );

        $form = new Varien_Data_Form();
        $this->_model->setForm($form);

        $this->assertEquals(
            Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Weight_Renderer::VIRTUAL_FIELD_HTML_ID,
            $this->_virtual->getId()
        );
        $this->assertEquals('is_virtual', $this->_virtual->getName());
        $this->assertEquals('Virtual / Downloadable', $this->_virtual->getLabel());
        $this->assertSame($form, $this->_virtual->getForm());
    }
}
