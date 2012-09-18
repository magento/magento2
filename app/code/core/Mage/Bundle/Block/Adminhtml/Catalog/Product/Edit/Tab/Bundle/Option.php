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
 * @package     Mage_Bundle
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Bundle option renderer
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Bundle_Option extends Mage_Adminhtml_Block_Widget
{
    /**
     * Form element
     *
     * @var Varien_Data_Form_Element_Abstract|null
     */
    protected $_element = null;

    /**
     * List of customer groups
     *
     * @deprecated since 1.7.0.0
     * @var array|null
     */
    protected $_customerGroups = null;

    /**
     * List of websites
     *
     * @deprecated since 1.7.0.0
     * @var array|null
     */
    protected $_websites = null;

    /**
     * List of bundle product options
     *
     * @var array|null
     */
    protected $_options = null;

    /**
     * Bundle option renderer class constructor
     *
     * Sets block template and necessary data
     */
    public function __construct()
    {
        $this->setTemplate('product/edit/bundle/option.phtml');
        $this->setCanReadPrice(true);
        $this->setCanEditPrice(true);
    }

    public function getFieldId()
    {
        return 'bundle_option';
    }

    public function getFieldName()
    {
        return 'bundle_options';
    }

    /**
     * Retrieve Product object
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->getData('product')) {
            $this->setData('product', Mage::registry('product'));
        }
        return $this->getData('product');
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function isMultiWebsites()
    {
        return !Mage::app()->hasSingleStore();
    }

    protected function _prepareLayout()
    {
        $this->setChild('add_selection_button',
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')
                ->setData(array(
                    'id'    => $this->getFieldId().'_{{index}}_add_button',
                    'label'     => Mage::helper('Mage_Bundle_Helper_Data')->__('Add Selection'),
                    'on_click'   => 'bSelection.showSearch(event)',
                    'class' => 'add'
                )));

        $this->setChild('close_search_button',
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')
                ->setData(array(
                    'id'    => $this->getFieldId().'_{{index}}_close_button',
                    'label'     => Mage::helper('Mage_Bundle_Helper_Data')->__('Close'),
                    'on_click'   => 'bSelection.closeSearch(event)',
                    'class' => 'back no-display'
                )));

        $this->setChild('option_delete_button',
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')
                ->setData(array(
                    'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Delete Option'),
                    'class' => 'delete delete-product-option',
                    'on_click' => 'bOption.remove(event)'
                ))
        );

        $this->setChild(
            'selection_template',
            $this->getLayout()->createBlock(
                'Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Bundle_Option_Selection'
            )
        );

        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getCloseSearchButtonHtml()
    {
        return $this->getChildHtml('close_search_button');
    }

    public function getAddSelectionButtonHtml()
    {
        return $this->getChildHtml('add_selection_button');
    }

    /**
     * Retrieve list of bundle product options
     *
     * @return array
     */
    public function getOptions()
    {
        if (!$this->_options) {
            $this->getProduct()->getTypeInstance()->setStoreFilter($this->getProduct()->getStoreId(),
                $this->getProduct());

            $optionCollection = $this->getProduct()->getTypeInstance()->getOptionsCollection($this->getProduct());

            $selectionCollection = $this->getProduct()->getTypeInstance()->getSelectionsCollection(
                $this->getProduct()->getTypeInstance()->getOptionsIds($this->getProduct()),
                $this->getProduct()
            );

            $this->_options = $optionCollection->appendSelections($selectionCollection);
            if ($this->getCanReadPrice() === false) {
                foreach ($this->_options as $option) {
                    if ($option->getSelections()) {
                        foreach ($option->getSelections() as $selection) {
                            $selection->setCanReadPrice($this->getCanReadPrice());
                            $selection->setCanEditPrice($this->getCanEditPrice());
                        }
                    }
                }
            }
        }
        return $this->_options;
    }

    public function getAddButtonId()
    {
        $buttonId = $this->getLayout()
                ->getBlock('admin.product.bundle.items')
                ->getChildBlock('add_button')->getId();
        return $buttonId;
    }

    public function getOptionDeleteButtonHtml()
    {
        return $this->getChildHtml('option_delete_button');
    }

    public function getSelectionHtml()
    {
        return $this->getChildHtml('selection_template');
    }

    public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Html_Select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{index}}_type',
                'class' => 'select select-product-option-type required-option-select',
                'extra_params' => 'onchange="bOption.changeType(event)"'
            ))
            ->setName($this->getFieldName().'[{{index}}][type]')
            ->setOptions(Mage::getSingleton('Mage_Bundle_Model_Source_Option_Type')->toOptionArray());

        return $select->getHtml();
    }

    public function getRequireSelectHtml()
    {
        $select = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Html_Select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{index}}_required',
                'class' => 'select'
            ))
            ->setName($this->getFieldName().'[{{index}}][required]')
            ->setOptions(Mage::getSingleton('Mage_Adminhtml_Model_System_Config_Source_Yesno')->toOptionArray());

        return $select->getHtml();
    }

    public function isDefaultStore()
    {
        return ($this->getProduct()->getStoreId() == '0');
    }
}
