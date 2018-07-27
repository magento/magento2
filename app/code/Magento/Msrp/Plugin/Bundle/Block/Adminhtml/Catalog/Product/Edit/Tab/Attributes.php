<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Plugin\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab;

use Magento\Msrp\Model\Product\Attribute\Source\Type\Price;

class Attributes
{
    /**
     * @param \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes $subject
     * @param \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes $result
     * @return \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes
     */
    public function afterSetForm(
        \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes $subject,
        \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes $result
    ) {
        $mapEnabled = $subject->getForm()->getElement('msrp');
        if ($mapEnabled && $subject->getCanEditPrice() !== false) {
            $mapEnabled->setAfterElementHtml(
                '<script>' .
                "
                require(['prototype'], function(){
                function changePriceTypeMap() {
                    if ($('price_type').value == " . \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC . ") {
                        $('msrp_display_actual_price_type').setValue(" . Price::TYPE_USE_CONFIG . ");
                        $('msrp_display_actual_price_type').disable();
                        $('msrp').setValue('');
                        $('msrp').disable();
                    } else {
                        $('msrp_display_actual_price_type').enable();
                        $('msrp').enable();
                    }
                }
                
                $('price_type').observe('change', changePriceTypeMap);
                changePriceTypeMap();

                });
                " .
                '</script>'
            );
        }
        return $result;
    }
}
