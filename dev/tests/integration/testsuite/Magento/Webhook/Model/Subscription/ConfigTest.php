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
 * @category    Magento
 * @package     Magento_Webhook
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Subscription;

/**
 * \Magento\Webhook\Model\Subscription\Config
 *
 * @magentoDbIsolation enabled
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * alias being used in the _files/config.xml file
     */
    const SUBSCRIPTION_ALIAS = 'subscription_alias';

    /**
     * name being used in the _files/config.xml file
     */
    const SUBSCRIPTION_NAME = 'Test subscriber';

    /**
     * @var \Magento\Webhook\Model\Subscription\Config
     */
    private $_config;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $configMock = $this->getMock('Magento\Webhook\Model\Config', array(), array(), '', false, false);
        $subscriptions = array(
            'subscription_alias' => array(
                'name' => 'Test subscriber',
                'endpoint_url' => 'http://mage.loc/mage-twitter-integration/web/index.php/endpoint',
                'topics' => array(
                    'customer' => array(
                        'created' => '',
                        'updated' => '',
                        'deleted' => '',
                    ),
                    'order' => array(
                        'created'
                    ),
                ),
            ),
        );
        $configMock->expects($this->any())->method('getSubscriptions')->will($this->returnValue($subscriptions));

        $this->_config = $objectManager->create('Magento\Webhook\Model\Subscription\Config', array(
            'config' => $configMock
        ));
    }

    public function testReadingConfig()
    {

        /** @var \Magento\Webhook\Model\Resource\Subscription\Collection $subscriberCollection */
        $subscriptionSet = $this->_objectManager->create('Magento\Webhook\Model\Resource\Subscription\Collection');

        // Sanity check
        $subscriptions = $subscriptionSet->getSubscriptionsByAlias(self::SUBSCRIPTION_ALIAS);
        $this->assertEmpty($subscriptions);
        $this->_config->updateSubscriptionCollection();

        // Test that data matches what we have in config.xml
        $subscriptions = $subscriptionSet->getSubscriptionsByAlias(self::SUBSCRIPTION_ALIAS);
        $this->assertEquals(1, count($subscriptions));
        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = array_shift($subscriptions);
        $this->assertEquals(self::SUBSCRIPTION_NAME, $subscription->getName());
    }
}
