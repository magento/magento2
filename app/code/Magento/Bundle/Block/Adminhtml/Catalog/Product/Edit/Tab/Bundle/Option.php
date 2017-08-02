<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option
 *
 * @since 2.0.0
 */
class Option extends \Magento\Backend\Block\Widget
{
    /**
     * Form element
     *
     * @var AbstractElement|null
     * @since 2.0.0
     */
    protected $_element = null;

    /**
     * List of bundle product options
     *
     * @var array|null
     * @since 2.0.0
     */
    protected $_options = null;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'product/edit/bundle/option.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Bundle\Model\Source\Option\Type
     * @since 2.0.0
     */
    protected $_optionTypes;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     * @since 2.0.0
     */
    protected $_yesno;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param \Magento\Bundle\Model\Source\Option\Type $optionTypes
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        \Magento\Bundle\Model\Source\Option\Type $optionTypes,
        \Magento\Framework\Registry $registry,
        array $data = []
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
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->setCanReadPrice(true);
        $this->setCanEditPrice(true);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getFieldId()
    {
        return 'bundle_option';
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getFieldName()
    {
        return 'bundle_options';
    }

    /**
     * Retrieve Product object
     *
     * @return \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    public function getProduct()
    {
        if (!$this->getData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * @param AbstractElement $element
     * @return $this
     * @since 2.0.0
     */
    public function setElement(AbstractElement $element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * @return AbstractElement|null
     * @since 2.0.0
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isMultiWebsites()
    {
        return !$this->_storeManager->hasSingleStore();
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'add_selection_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'id' => $this->getFieldId() . '_<%- data.index %>_add_button',
                'label' => __('Add Products to Option'),
                'class' => 'add add-selection'
            ]
        );

        $this->addChild(
            'close_search_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'id' => $this->getFieldId() . '_<%- data.index %>_close_button',
                'label' => __('Close'),
                'on_click' => 'bSelection.closeSearch(event)',
                'class' => 'back no-display'
            ]
        );

        $this->addChild(
            'option_delete_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Delete Option'), 'class' => 'action-delete', 'on_click' => 'bOption.remove(event)']
        );

        $this->addChild(
            'selection_template',
            \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Selection::class
        );

        return parent::_prepareLayout();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getCloseSearchButtonHtml()
    {
        return $this->getChildHtml('close_search_button');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getAddSelectionButtonHtml()
    {
        return $this->getChildHtml('add_selection_button');
    }

    /**
     * Retrieve list of bundle product options
     *
     * @return array
     * @since 2.0.0
     */
    public function getOptions()
    {
        if (!$this->_options) {
            /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionCollection */
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

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getAddButtonId()
    {
        $buttonId = $this->getLayout()->getBlock('admin.product.bundle.items')->getChildBlock('add_button')->getId();
        return $buttonId;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getOptionDeleteButtonHtml()
    {
        return $this->getChildHtml('option_delete_button');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getSelectionHtml()
    {
        return $this->getChildHtml('selection_template');
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            [
                'id' => $this->getFieldId() . '_<%- data.index %>_type',
                'class' => 'select select-product-option-type required-option-select',
                'extra_params' => 'onchange="bOption.changeType(event)"',
            ]
        )->setName(
            $this->getFieldName() . '[<%- data.index %>][type]'
        )->setOptions(
            $this->_optionTypes->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getRequireSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            ['id' => $this->getFieldId() . '_<%- data.index %>_required', 'class' => 'select']
        )->setName(
            $this->getFieldName() . '[<%- data.index %>][required]'
        )->setOptions(
            $this->_yesno->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isDefaultStore()
    {
        return $this->getProduct()->getStoreId() == '0';
    }
}
