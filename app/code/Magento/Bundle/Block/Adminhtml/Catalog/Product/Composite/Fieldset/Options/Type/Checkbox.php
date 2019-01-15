<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type;

/**
 * Bundle option checkbox type renderer
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Checkbox extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Checkbox
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Bundle::product/composite/fieldset/options/type/checkbox.phtml';

    /**
     * @inheritdoc
     */
    public function setValidationContainer($elementId, $containerId)
    {
        return '<script>
            document.getElementById(\'' .
            $elementId .
            '\').advaiceContainer = \'' .
            $containerId .
            '\';
            </script>';
    }

    /**
     * @inheritdoc
     */
    public function getSelectionPrice($selection)
    {
        $price = parent::getSelectionPrice($selection);
        $qty = $selection->getSelectionQty();

        return $price * $qty;
    }
}
