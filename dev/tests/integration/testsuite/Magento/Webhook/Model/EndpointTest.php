<?php
/**
 * \Magento\Webhook\Model\Endpoint
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model;

class EndpointTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMethods()
    {
        /** @var  \Magento\Webhook\Model\Endpoint $endpoint */
        $endpoint = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Endpoint');

        $endpoint->setEndpointUrl('endpoint.url.com');
        $this->assertEquals('endpoint.url.com', $endpoint->getEndpointUrl());

        $endpoint->setTimeoutInSecs('9001');
        $this->assertEquals('9001', $endpoint->getTimeoutInSecs());

        $endpoint->setFormat('JSON');
        $this->assertEquals('JSON', $endpoint->getFormat());

        $endpoint->setAuthenticationType('basic');
        $this->assertEquals('basic', $endpoint->getAuthenticationType());

        // test getUser
        $endpoint->setApiUserId(null);
        $this->assertEquals(null, $endpoint->getUser());

        $userId = 42;
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\User', array('webapiUserId' => $userId));
        $endpoint->setApiUserId($userId);
        $this->assertEquals($user, $endpoint->getUser());

    }

    public function testBeforeSave()
    {
        /** @var  \Magento\Webhook\Model\Endpoint $endpoint */
        $endpoint = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Endpoint');
        $endpoint->setUpdatedAt('-1')
            ->save();

        $this->assertEquals('none', $endpoint->getAuthenticationType());
        $this->assertFalse($endpoint->getUpdatedAt() == '-1');
        $endpoint->delete();
    }
}
