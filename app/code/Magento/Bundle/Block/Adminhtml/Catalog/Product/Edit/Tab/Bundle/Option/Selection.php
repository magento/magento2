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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option;

/**
 * Bundle selection renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Selection extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'product/edit/bundle/option/selection.phtml';

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Bundle\Model\Source\Option\Selection\Price\Type
     */
    protected $_priceType;

    /**
     * @var \Magento\Backend\Model\Config\Source\Yesno
     */
    protected $_yesno;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Config\Source\Yesno $yesno
     * @param \Magento\Bundle\Model\Source\Option\Selection\Price\Type $priceType
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Config\Source\Yesno $yesno,
        \Magento\Bundle\Model\Source\Option\Selection\Price\Type $priceType,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_catalogData = $catalogData;
        $this->_coreRegistry = $registry;
        $this->_priceType = $priceType;
        $this->_yesno = $yesno;
        parent::__construct($context, $data);
    }

    /**
     * Initialize bundle option selection block
     *
     * @return void
     */
    protected function _construct()
    {

        $this->setCanReadPrice(true);
        $this->setCanEditPrice(true);
    }

    /**
     * Return field id
     *
     * @return string
     */
    public function getFieldId()
    {
        return 'bundle_selection';
    }

    /**
     * Return field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'bundle_selections';
    }

    /**
     * Prepare block layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'selection_delete_button',
            'Magento\Backend\Block\Widget\Button',
            array('label' => __('Delete'), 'class' => 'delete icon-btn', 'on_click' => 'bSelection.remove(event)')
        );
        return parent::_prepareLayout();
    }

    /**
     * Retrieve delete button html
     *
     * @return string
     */
    public function getSelectionDeleteButtonHtml()
    {
        return $this->getChildHtml('selection_delete_button');
    }

    /**
     * Retrieve price type select html
     *
     * @return string
     */
    public function getPriceTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setData(
            array(
                'id' => $this->getFieldId() . '_{{index}}_price_type',
                'class' => 'select select-product-option-type required-option-select'
            )
        )->setName(
            $this->getFieldName() . '[{{parentIndex}}][{{index}}][selection_price_type]'
        )->setOptions(
            $this->_priceType->toOptionArray()
        );
        if ($this->getCanEditPrice() === false) {
            $select->setExtraParams('disabled="disabled"');
        }
        return $select->getHtml();
    }

    /**
     * Retrieve qty type select html
     *
     * @return string
     */
    public function getQtyTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setData(
            array('id' => $this->getFieldId() . '_{{index}}_can_change_qty', 'class' => 'select')
        )->setName(
            $this->getFieldName() . '[{{parentIndex}}][{{index}}][selection_can_change_qty]'
        )->setOptions(
            $this->_yesno->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * Return search url
     *
     * @return string
     */
    public function getSelectionSearchUrl()
    {
        return $this->getUrl('adminhtml/bundle_selection/grid');
    }

    /**
     * Check if used website scope price
     *
     * @return string
     */
    public function isUsedWebsitePrice()
    {
        $product = $this->_coreRegistry->registry('product');
        return !$this->_catalogData->isPriceGlobal() && $product->getStoreId();
    }

    /**
     * Retrieve price scope checkbox html
     *
     * @return string
     */
    public function getCheckboxScopeHtml()
    {
        $checkboxHtml = '';
        if ($this->isUsedWebsitePrice()) {
            $fieldsId = $this->getFieldId() . '_{{index}}_price_scope';
            $name = $this->getFieldName() . '[{{parentIndex}}][{{index}}][default_price_scope]';
            $class = 'bundle-option-price-scope-checkbox';
            $label = __('Use Default Value');
            $disabled = $this->getCanEditPrice() === false ? ' disabled="disabled"' : '';
            $checkboxHtml = '<input type="checkbox" id="' .
                $fieldsId .
                '" class="' .
                $class .
                '" name="' .
                $name .
                '"' .
                $disabled .
                ' value="1" />';
            $checkboxHtml .= '<label class="normal" for="' . $fieldsId . '">' . $label . '</label>';
        }
        return $checkboxHtml;
    }
}
