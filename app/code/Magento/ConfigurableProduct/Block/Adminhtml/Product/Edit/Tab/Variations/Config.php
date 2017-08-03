<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations;

use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Adminhtml catalog super product configurable tab
 */
class Config extends Widget implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit/super/config.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var Configurable
     * @since 2.1.0
     */
    protected $configurableType;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param Configurable $configurableType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        Configurable $configurableType,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->configurableType = $configurableType;
        parent::__construct($context, $data);
    }

    /**
     * Initialize block
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setProductId($this->getRequest()->getParam('id'));

        $this->setId('config_super_product');
        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
    }

    /**
     * Retrieve Tab class (for loading)
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Retrieve Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Configurations');
    }

    /**
     * Retrieve Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Configurations');
    }

    /**
     * Can show tab flag
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check is a hidden tab
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get parent tab code
     *
     * @return string
     */
    public function getParentTab()
    {
        return 'product-details';
    }

    /**
     * @return bool
     * @since 2.1.0
     */
    public function isHasVariations()
    {
        return $this->getProduct()->getTypeId() === Configurable::TYPE_CODE
            && $this->configurableType->getUsedProducts($this->getProduct());
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setData('opened', $this->getProduct()->getTypeId() === Configurable::TYPE_CODE);
        return parent::_prepareLayout();
    }
}
