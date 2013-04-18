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
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Recursive renderer that uses several templates
 *
 * @method string getHtml()
 * @method Mage_DesignEditor_Block_Adminhtml_Editor_Form_Renderer_Recursive setHtml($html)
 */
class Mage_DesignEditor_Block_Adminhtml_Editor_Form_Renderer_Recursive extends Mage_Backend_Block_Template
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Form element to render
     *
     * @var Varien_Data_Form_Element_Abstract
     */
    protected $_element;

    /**
     * Path to template file in theme.
     *
     * Recursive renderer use '_template' property for rendering templates one by one
     *
     * @var string
     */
    protected $_template = null;

    /**
     * Set of templates to render
     *
     * Upper is rendered first and is inserted into next using <?php echo $this->getHtml() ?>
     *
     * @var array
     */
    protected $_templates = array();

    /**
     * Get element renderer bound to
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Render form element as HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;

        foreach ($this->_templates as $template) {
            $this->setTemplate($template);
            $this->setHtml($this->toHtml());
        }

        return $this->getHtml();
    }
}
