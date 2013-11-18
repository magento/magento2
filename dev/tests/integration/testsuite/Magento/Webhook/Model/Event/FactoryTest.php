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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Event;

/**
 * \Magento\Webhook\Model\Event\Factory
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $factory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Event\Factory');
        $data = array('array', 'of', 'event', 'data');
        $topic = 'Topic on which to publish data';

        /** @var \Magento\Webhook\Model\Event $event */
        $event = $factory->create($topic, $data);

        $this->assertInstanceOf('Magento\Webhook\Model\Event', $event);
        $this->assertEquals($topic, $event->getTopic());
        $this->assertEquals($data, $event->getBodyData());
    }

    public function testCreateEmpty()
    {
        $factory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Event\Factory');

        /** @var \Magento\Webhook\Model\Event $event */
        $event = $factory->create('', array());

        $this->assertInstanceOf('Magento\Webhook\Model\Event', $event);
        $this->assertEmpty($event->getBodyData());
        $this->assertEmpty($event->getTopic());
    }
}
