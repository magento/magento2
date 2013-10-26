<?php
/**
 * \Magento\Webhook\Model\Resource\Job\Collection
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Resource\Job;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        /** @var \Magento\Event\ManagerInterface $eventManager */
        $eventManager = $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false);
        /** @var \Magento\Data\Collection\Db\FetchStrategyInterface $mockFetchStrategy */
        $mockFetchStrategy = $this->getMockBuilder('Magento\Data\Collection\Db\FetchStrategyInterface')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Core\Model\EntityFactory $entityFactory */
        $entityFactory = $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false);
        $mockDBAdapter = $this->getMockBuilder('Magento\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array('_connect', '_quote'))
            ->getMockForAbstractClass();
        $mockResourceEvent = $this->getMockBuilder('Magento\Webhook\Model\Resource\Job')
            ->disableOriginalConstructor()
            ->getMock();
        $mockResourceEvent->expects($this->once())
            ->method('getReadConnection')
            ->will($this->returnValue($mockDBAdapter));
        $logger = $this->getMock('Magento\Core\Model\Logger', array(), array(), '', false);

        $collection = new \Magento\Webhook\Model\Resource\Job\Collection(
            $eventManager, $logger, $mockFetchStrategy, $entityFactory, $mockResourceEvent
        );
        $this->assertInstanceOf('Magento\Webhook\Model\Resource\Job\Collection', $collection);
        $this->assertEquals('Magento\Webhook\Model\Resource\Job', $collection->getResourceModelName());
    }
}
