<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * customers defined options
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type;

class AbstractType extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_name = 'abstract';

    /**
     * @var \Magento\Catalog\Model\Config\Source\Product\Options\Price
     */
    protected $_optionPrice;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\Config\Source\Product\Options\Price $optionPrice
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Config\Source\Product\Options\Price $optionPrice,
        array $data = []
    ) {
        $this->_optionPrice = $optionPrice;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'option_price_type',
            $this->getLayout()->addBlock(
                \Magento\Framework\View\Element\Html\Select::class,
                $this->getNameInLayout() . '.option_price_type',
                $this->getNameInLayout()
            )->setData(
                [
                    'id' => 'product_option_<%- data.option_id %>_price_type',
                    'class' => 'select product-option-price-type',
                ]
            )
        );

        $this->getChildBlock(
            'option_price_type'
        )->setName(
            'product[options][<%- data.option_id %>][price_type]'
        )->setOptions(
            $this->_optionPrice->toOptionArray()
        );

        return parent::_prepareLayout();
    }

    /**
     * Get html of Price Type select element
     *
     * @param string $extraParams
     * @return string
     */
    public function getPriceTypeSelectHtml($extraParams = '')
    {
        if ($this->getCanEditPrice() === false) {
            $extraParams .= ' disabled="disabled"';
            $this->getChildBlock('option_price_type');
        }
        $this->getChildBlock('option_price_type')->setExtraParams($extraParams);

        return $this->getChildHtml('option_price_type');
    }
}
