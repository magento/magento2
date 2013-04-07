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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Block_Adminhtml_Subscriber_Edit_Test_Form extends Mage_Backend_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                'id' => 'test_form',
                'action' => $this->getUrl('*/*/test', array()),
                'method' => 'post'
            )
        );

        $fieldset = $form->addFieldset('test_fieldset', array('legend' => $this->__('Test')));

        $fieldset->addField('test_button', 'submit', array(
            'label'     => $this->__('Send Test Message'),
            'name'      => 'test_button',
            'value'     => $this->__('Start'),
        ));

        $form->setUseContainer(true);
//        $form->setValues($values);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}