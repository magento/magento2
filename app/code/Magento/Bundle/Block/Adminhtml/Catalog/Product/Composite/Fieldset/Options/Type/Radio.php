<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type;

/**
 * Bundle option radiobox type renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Radio extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Radio
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Bundle::product/composite/fieldset/options/type/radio.phtml';

    /**
     * @param  string $elementId
     * @param  string $containerId
     * @return string
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
