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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer form xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Customer_Form extends Mage_Core_Block_Template
{
    /**
     * Render customer form xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        $editFlag = (int)$this->getRequest()->getParam('edit');
        $customer  = $this->getCustomer();
        /** @var $xmlModel Mage_XmlConnect_Model_Simplexml_Element */
        $xmlModel  = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', '<node></node>');
        //Enterprise_Customer
        if ($editFlag == 1 && $customer && $customer->getId()) {
            $firstname = $xmlModel->xmlentities($customer->getFirstname());
            $lastname  = $xmlModel->xmlentities($customer->getLastname());
            $email     = $xmlModel->xmlentities($customer->getEmail());
        } else {
            $firstname = $lastname = $email = '';
        }

        if ($editFlag) {
            $passwordManageXml = '
                   <field name="change_password" type="checkbox" label="' . $xmlModel->xmlentities($this->__('Change Password')) . '"/>
                </fieldset>
                <fieldset>
                    <field name="current_password" type="password" label="' . $xmlModel->xmlentities($this->__('Current Password')) . '"/>
                    <field name="password" type="password" label="' . $xmlModel->xmlentities($this->__('New Password')) . '"/>
                    <field name="confirmation" type="password" label="' . $xmlModel->xmlentities($this->__('Confirm New Password')) . '">
                        <validators>
                            <validator type="confirmation" message="' . $xmlModel->xmlentities($this->__('Regular and confirmation passwords must be equal')) . '">password</validator>
                        </validators>
                    </field>
                </fieldset>';
        } else {
            $passwordManageXml = '
                    <field name="password" type="password" label="' . $xmlModel->xmlentities($this->__('Password')) . '" required="true"/>
                    <field name="confirmation" type="password" label="' . $xmlModel->xmlentities($this->__('Confirm Password')) . '" required="true">
                        <validators>
                            <validator type="confirmation" message="' . $xmlModel->xmlentities($this->__('Regular and confirmation passwords must be equal')) . '">password</validator>
                        </validators>
                    </field>
                </fieldset>';
        }

        $xml = <<<EOT
<form name="account_form" method="post">
    <fieldset>
        <field name="firstname" type="text" label="{$xmlModel->xmlentities($this->__('First Name'))}" required="true" value="$firstname" />
        <field name="lastname" type="text" label="{$xmlModel->xmlentities($this->__('Last Name'))}" required="true" value="$lastname" />
        <field name="email" type="text" label="{$xmlModel->xmlentities($this->__('Email'))}" required="true" value="$email">
            <validators>
                <validator type="email" message="{$xmlModel->xmlentities($this->__('Wrong email format'))}"/>
            </validators>
        </field>
        $passwordManageXml
</form>
EOT;

        return $xml;
    }
}
