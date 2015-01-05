<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
    protected $_template = 'product/composite/fieldset/options/type/radio.phtml';

    /**
     * @param  string $elementId
     * @param  string $containerId
     * @return string
     */
    public function setValidationContainer($elementId, $containerId)
    {
        return '<script type="text/javascript">
            $(\'' .
            $elementId .
            '\').advaiceContainer = \'' .
            $containerId .
            '\';
            </script>';
    }
}
