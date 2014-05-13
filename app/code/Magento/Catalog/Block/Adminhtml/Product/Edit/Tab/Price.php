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
 * Adminhtml product edit price block
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab;

class Price extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @return void
     */
    protected function _prepareForm()
    {
        $product = $this->_coreRegistry->registry('product');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('tiered_price', array('legend' => __('Tier Pricing')));

        $fieldset->addField(
            'default_price',
            'label',
            array(
                'label' => __('Default Price'),
                'title' => __('Default Price'),
                'name' => 'default_price',
                'bold' => true,
                'value' => $product->getPrice()
            )
        );

        $fieldset->addField(
            'tier_price',
            'text',
            array('name' => 'tier_price', 'class' => 'requried-entry', 'value' => $product->getData('tier_price'))
        );

        $form->getElement(
            'tier_price'
        )->setRenderer(
            $this->getLayout()->createBlock('Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Tier')
        );

        $this->setForm($form);
    }
}
