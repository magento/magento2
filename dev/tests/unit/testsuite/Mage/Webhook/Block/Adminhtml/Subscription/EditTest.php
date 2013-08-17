<?php
/**
 * Mage_Webhook_Block_Adminhtml_Subscription_Edit
 *
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
class Mage_Webhook_Block_Adminhtml_Subscription_EditTest extends Magento_Test_Block_Adminhtml
{
    /** @var  Mage_Core_Model_Registry */
    private $_registry;

    /** @var  Mage_Webhook_Block_Adminhtml_Subscription_Edit */
    private $_block;

    public function testGetHeaderTestExisting()
    {
        $subscriptionData = array(
            Mage_Webhook_Block_Adminhtml_Subscription_Edit::DATA_SUBSCRIPTION_ID => true,
            'alias' => 'alias_value');
        $this->_registry = new Mage_Core_Model_Registry();
        $this->_registry->register(Mage_Webhook_Block_Adminhtml_Subscription_Edit::REGISTRY_KEY_CURRENT_SUBSCRIPTION,
            $subscriptionData);

        $this->_block = new Mage_Webhook_Block_Adminhtml_Subscription_Edit(
            $this->_registry,
            $this->_context
        );
        $this->assertEquals('Edit Subscription', $this->_block->getHeaderText());

        $this->_registry->unregister(Mage_Webhook_Block_Adminhtml_Subscription_Edit::REGISTRY_KEY_CURRENT_SUBSCRIPTION);
    }

    public function testGetHeaderTestNew()
    {
        $this->_registry = new Mage_Core_Model_Registry();
        $this->_block = new Mage_Webhook_Block_Adminhtml_Subscription_Edit(
            $this->_registry,
            $this->_context
        );

        $this->assertEquals('Add Subscription', $this->_block->getHeaderText());
    }
}