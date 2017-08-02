<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type;

/**
 * Bundle option radiobox type renderer
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Radio extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Radio
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'product/composite/fieldset/options/type/radio.phtml';

    /**
     * @param  string $elementId
     * @param  string $containerId
     * @return string
     * @since 2.0.0
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
}
