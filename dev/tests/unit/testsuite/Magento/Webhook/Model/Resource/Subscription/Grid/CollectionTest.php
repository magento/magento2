<?php
/**
 * \Magento\Webhook\Model\Resource\Subscription\Grid\Collection
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
namespace Magento\Webhook\Model\Resource\Subscription\Grid;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $eventManager = $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false);

        $fetchStrategyMock = $this->_makeMock('Magento\Data\Collection\Db\FetchStrategyInterface');
        $endpointResMock = $this->_makeMock('Magento\Webhook\Model\Resource\Endpoint');

        $configMock = $this->_makeMock('Magento\Webhook\Model\Subscription\Config');
        $configMock->expects($this->once())
            ->method('updateSubscriptionCollection');

        $selectMock = $this->_makeMock('Zend_Db_Select');
        $selectMock->expects($this->any())
            ->method('from')
            ->with(array('main_table' => null));
        $connectionMock = $this->_makeMock('Magento\DB\Adapter\Pdo\Mysql');
        $connectionMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($selectMock));
        $resourceMock = $this-> _makeMock('Magento\Webhook\Model\Resource\Subscription');
        $resourceMock->expects($this->any())
            ->method('getReadConnection')
            ->will($this->returnValue($connectionMock));
        /** @var \Magento\Core\Model\EntityFactory $entityFactory */
        $entityFactory = $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false);
        $logger = $this->getMock('Magento\Core\Model\Logger', array(), array(), '', false);
        new \Magento\Webhook\Model\Resource\Subscription\Grid\Collection(
            $configMock, $endpointResMock, $eventManager, $logger, $fetchStrategyMock, $entityFactory, $resourceMock);
    }

    /**
     * Generates a mock object of the given class
     *
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function _makeMock($className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }

}
