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

namespace Magento\Newsletter\Model\Resource\Subscriber;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Newsletter\Model\Resource\Subscriber\Collection
     */
    protected $_collectionModel;

    protected function setUp()
    {
        $this->_collectionModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Newsletter\Model\Resource\Subscriber\Collection');
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testShowCustomerInfo()
    {
        $this->_collectionModel->showCustomerInfo()->load();

        /** @var \Magento\Newsletter\Model\Subscriber[] $subscribers */
        $subscribers = $this->_collectionModel->getItems();
        $this->assertCount(2, $subscribers);
        $subscriber = array_shift($subscribers);
        $this->assertEquals('Firstname', $subscriber->getCustomerFirstname(), $subscriber->getSubscriberEmail());
        $this->assertEquals('Lastname', $subscriber->getCustomerLastname(), $subscriber->getSubscriberEmail());
        $subscriber = array_shift($subscribers);
        $this->assertNull($subscriber->getCustomerFirstname(), $subscriber->getSubscriberEmail());
        $this->assertNull($subscriber->getCustomerLastname(), $subscriber->getSubscriberEmail());
    }
}
