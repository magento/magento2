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
                '<script type="text/javascript">' .
                "
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
                document.observe('dom:loaded', function() {
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
