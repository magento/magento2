<?php
/**
 * Mage_Webhook_Block_Adminhtml_Registration_ActivateTest
 *
 * @magentoDbIsolation enabled
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
class Mage_Webhook_Block_Adminhtml_Registration_ActivateTest extends PHPUnit_Framework_TestCase
{
    public function testGetMethods()
    {
        // Data for the block object
        $topics = array('array', 'of', 'topics');
        $subscriptionId = Mage::getObjectManager()->create('Mage_Webhook_Model_Subscription')
            ->setDataChanges(true)
            ->save()
            ->getId();
        $subscriptionData = array(
            Mage_Webhook_Block_Adminhtml_Registration_Activate::DATA_SUBSCRIPTION_ID => $subscriptionId,
            Mage_Webhook_Block_Adminhtml_Registration_Activate::DATA_NAME => 'name',
            Mage_Webhook_Block_Adminhtml_Registration_Activate::DATA_TOPICS => $topics
        );

        /** @var Mage_Core_Model_Registry $registry */
        $registry = Mage::getObjectManager()->get('Mage_Core_Model_Registry');
        $registry->register(Mage_Webhook_Block_Adminhtml_Registration_Activate::REGISTRY_KEY_CURRENT_SUBSCRIPTION,
            $subscriptionData);

        /** @var Mage_Core_Block_Template_Context $context */
        $context = Mage::getObjectManager()->create('Mage_Core_Block_Template_Context');

        /** @var Mage_Webhook_Block_Adminhtml_Registration_Activate $block */
        $block = Mage::getObjectManager()->create('Mage_Webhook_Block_Adminhtml_Registration_Activate', array(
            $context,
            $registry
        ));

        $urlBuilder = $context->getUrlBuilder();
        $expectedUrl = $urlBuilder->getUrl('*/*/accept', array('id' => $subscriptionId));

        $this->assertEquals($expectedUrl, $block->getAcceptUrl());
        $this->assertEquals('name', $block->getSubscriptionName());
        $this->assertEquals($topics, $block->getSubscriptionTopics());
    }
}