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
 * @package     Magento_Bundle
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Bundle option renderer
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle;

class Option extends \Magento\Backend\Block\Widget
{
    /**
     * Form element
     *
     * @var \Magento\Data\Form\Element\AbstractElement|null
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

    protected $_template = 'product/edit/bundle/option.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Bundle\Model\Source\Option\Type
     */
    protected $_optionTypes;

    /**
     * @var \Magento\Backend\Model\Config\Source\Yesno
     */
    protected $_yesno;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Config\Source\Yesno $yesno
     * @param \Magento\Bundle\Model\Source\Option\Type $optionTypes
     * @param \Magento\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Config\Source\Yesno $yesno,
        \Magento\Bundle\Model\Source\Option\Type $optionTypes,
        \Magento\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        $this->_optionTypes = $optionTypes;
        $this->_yesno = $yesno;
        parent::__construct($context, $data);
    }

    /**
     * Bundle option renderer class constructor
     *
     * Sets block template and necessary data
     */
    protected function _construct()
    {

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
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->getData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }

    public function render(\Magento\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function setElement(\Magento\Data\Form\Element\AbstractElement $element)
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
        return !$this->_storeManager->hasSingleStore();
    }

    protected function _prepareLayout()
    {
        $this->addChild('add_selection_button', 'Magento\Backend\Block\Widget\Button', array(
            'id'    => $this->getFieldId() . '_{{index}}_add_button',
            'label' => __('Add Products to Option'),
            'class' => 'add add-selection'
        ));

        $this->addChild('close_search_button', 'Magento\Backend\Block\Widget\Button', array(
            'id'    => $this->getFieldId().'_{{index}}_close_button',
            'label'     => __('Close'),
            'on_click'   => 'bSelection.closeSearch(event)',
            'class' => 'back no-display'
        ));

        $this->addChild('option_delete_button', 'Magento\Backend\Block\Widget\Button', array(
            'label' => __('Delete Option'),
            'class' => 'action-delete',
            'on_click' => 'bOption.remove(event)'
        ));

        $this->addChild(
            'selection_template',
            'Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Selection'
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
        $select = $this->getLayout()->createBlock('Magento\View\Element\Html\Select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{index}}_type',
                'class' => 'select select-product-option-type required-option-select',
                'extra_params' => 'onchange="bOption.changeType(event)"'
            ))
            ->setName($this->getFieldName().'[{{index}}][type]')
            ->setOptions($this->_optionTypes->toOptionArray());

        return $select->getHtml();
    }

    public function getRequireSelectHtml()
    {
        $select = $this->getLayout()->createBlock('Magento\View\Element\Html\Select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{index}}_required',
                'class' => 'select'
            ))
            ->setName($this->getFieldName().'[{{index}}][required]')
            ->setOptions($this->_yesno->toOptionArray());

        return $select->getHtml();
    }

    public function isDefaultStore()
    {
        return $this->getProduct()->getStoreId() == '0';
    }
}
