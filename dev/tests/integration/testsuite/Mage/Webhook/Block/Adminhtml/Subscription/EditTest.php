<?php
/**
 * Mage_Webhook_Block_AdminHtml_Subscription_Edit
 *
 * @magentoAppArea adminhtml
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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Block_Adminhtml_Subscription_EditTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Core_Model_Registry */
    private $_registry;

    public function setUp()
    {
        $this->_registry = Mage::getObjectManager()->create('Mage_Core_Model_Registry');
    }

    public function tearDown()
    {
        $this->_registry->unregister('current_subscription');
    }

    public function testAddSubscriptionTitle()
    {
        /** @var Mage_Core_Model_Layout $layout */
        $layout = Mage::getObjectManager()->create('Mage_Core_Model_Layout');

        $subscription = array(
            'subscription_id' => null,
        );
        $this->_registry->register('current_subscription', $subscription);

        /** @var Mage_Webhook_Block_Adminhtml_Subscription_Edit $block */
        $block = $layout->createBlock('Mage_Webhook_Block_Adminhtml_Subscription_Edit',
            '', array('registry' => $this->_registry)
        );
        $block->toHtml();
        $this->assertEquals('Add Subscription', $block->getHeaderText());

    }

    public function testEditSubscriptionTitle()
    {
        /** @var Mage_Core_Model_Layout $layout */
        $layout = Mage::getObjectManager()->create('Mage_Core_Model_Layout');

        $subscription = array(
            'subscription_id' => 1,
        );
        $this->_registry->register('current_subscription', $subscription);

        /** @var Mage_Webhook_Block_Adminhtml_Subscription_Edit $block */
        $block = $layout->createBlock('Mage_Webhook_Block_Adminhtml_Subscription_Edit',
            '', array('registry' => $this->_registry)
        );
        $block->toHtml();
        $this->assertEquals('Edit Subscription', $block->getHeaderText());
    }
}