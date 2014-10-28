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
        array $data = array()
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
                'Magento\Framework\View\Element\Html\Select',
                $this->getNameInLayout() . '.option_price_type',
                $this->getNameInLayout()
            )->setData(
                array('id' => 'product_option_${option_id}_price_type', 'class' => 'select product-option-price-type')
            )
        );

        $this->getChildBlock(
            'option_price_type'
        )->setName(
            'product[options][${option_id}][price_type]'
        )->setOptions(
            $this->_optionPrice->toOptionArray()
        );

        return parent::_prepareLayout();
    }

    /**
     * Get html of Price Type select element
     *
     * @return string
     */
    public function getPriceTypeSelectHtml()
    {
        if ($this->getCanEditPrice() === false) {
            $this->getChildBlock('option_price_type')->setExtraParams('disabled="disabled"');
        }
        return $this->getChildHtml('option_price_type');
    }
}
