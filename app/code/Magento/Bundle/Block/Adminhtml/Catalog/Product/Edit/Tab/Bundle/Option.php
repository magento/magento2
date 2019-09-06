<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\Store;

/**
 * Block for rendering option of bundle product
 *
 * Class \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option
 */
class Option extends \Magento\Backend\Block\Widget
{
    /**
     * Form element
     *
     * @var AbstractElement|null
     */
    protected $_element = null;

    /**
     * List of bundle product options
     *
     * @var array|null
     */
    protected $_options = null;

    /**
     * @var string
     */
    protected $_template = 'Magento_Bundle::product/edit/bundle/option.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Bundle\Model\Source\Option\Type
     */
    protected $_optionTypes;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_yesno;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param \Magento\Bundle\Model\Source\Option\Type $optionTypes
     * @param \Magento\Framework\Registry $registry
     * @param array $data
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
     */
    protected function _construct()
    {
        $this->setCanReadPrice(true);
        $this->setCanEditPrice(true);
    }

    /**
     * Return Field Id
     *
     * @return string
     */
    public function getFieldId()
    {
        return 'bundle_option';
    }

    /**
     * Return Field Name
     *
     * @return string
     */
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

    /**
     * Render
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * Set Element
     *
     * @param AbstractElement $element
     * @return $this
     */
    public function setElement(AbstractElement $element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * Get Element
     *
     * @return AbstractElement|null
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Return Is Multi Websites
     *
     * @return bool
     */
    public function isMultiWebsites()
    {
        return !$this->_storeManager->hasSingleStore();
    }

    /**
     * Prepare Layout
     *
     * @return $this
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
     * Get Add Button Html
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Get Close Search Button Html
     *
     * @return string
     */
    public function getCloseSearchButtonHtml()
    {
        return $this->getChildHtml('close_search_button');
    }

    /**
     * Get Add Selection Button Html
     *
     * @return string
     */
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
     * Get Add Button Id
     *
     * @return mixed
     */
    public function getAddButtonId()
    {
        $buttonId = $this->getLayout()->getBlock('admin.product.bundle.items')->getChildBlock('add_button')->getId();
        return $buttonId;
    }

    /**
     * Get Options Delete Button Html
     *
     * @return string
     */
    public function getOptionDeleteButtonHtml()
    {
        return $this->getChildHtml('option_delete_button');
    }

    /**
     * Get Selection Html
     *
     * @return string
     */
    public function getSelectionHtml()
    {
        return $this->getChildHtml('selection_template');
    }

    /**
     * Get Type Select Html
     *
     * @return mixed
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
     * Get Require Select Html
     *
     * @return mixed
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
     * Return Is Default Store
     *
     * @return bool
     */
    public function isDefaultStore()
    {
        return $this->getProduct()->getStoreId() == Store::DEFAULT_STORE_ID;
    }
}
