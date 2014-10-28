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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\OfflineShipping\Model\Observer\SalesRule;

use Magento\OfflineShipping\Model\SalesRule\Rule;

/**
 * Checkout cart shipping block plugin
 *
 * @author    Magento Core Team <core@magentocommerce.com>
 */
class ActionsTab
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function prepareForm($observer)
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $observer->getForm();
        foreach ($form->getElements() as $element) {
            /** @var \Magento\Framework\Data\Form\Element\AbstractElement $element */
            if ($element->getId() == 'action_fieldset') {
                $element->addField(
                    'simple_free_shipping',
                    'select',
                    array(
                        'label' => __('Free Shipping'),
                        'title' => __('Free Shipping'),
                        'name' => 'simple_free_shipping',
                        'options' => array(
                            0 => __('No'),
                            Rule::FREE_SHIPPING_ITEM => __('For matching items only'),
                            Rule::FREE_SHIPPING_ADDRESS => __('For shipment with matching items')
                        )
                    )
                );
            }
        }
    }
}
