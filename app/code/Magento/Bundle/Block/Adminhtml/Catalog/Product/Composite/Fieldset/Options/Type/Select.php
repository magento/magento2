<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type;

/**
 * Bundle option dropdown type renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Select extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Select
{
    /**
     * @var string
     */
    protected $_template = 'product/composite/fieldset/options/type/select.phtml';

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
