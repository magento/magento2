<?php
/**
 * \Magento\Webhook\Model\Resource\Endpoint
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
namespace Magento\Webhook\Model\Resource;

class EndpointTest extends \PHPUnit_Framework_TestCase
{
    const TABLE_NAME = 'outbound_endpoint_table';

    /** @var  \Magento\Webhook\Model\Resource\Endpoint */
    private $_endpoint;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_adapterMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_selectMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_resourceMock;

    /** @var string[] */
    private $_apiUserIds = array('api_user_id1', 'api_user_id2', 'api_user_id3');

    protected function setUp()
    {
        // Select mock
        $this->_selectMock = $this->_makeMock('Magento\DB\Select');
        // Select stubs
        $this->_selectMock->expects($this->once())
            ->method('from')
            ->with(self::TABLE_NAME, array('endpoint_id'))
            ->will($this->returnSelf());

        // Adapter mock
        $this->_adapterMock = $this->_makeMock('Magento\DB\Adapter\Pdo\Mysql');
        // Adapter stubs
        $this->_adapterMock->expects($this->once())
            ->method('select')
            ->with()
            ->will($this->returnValue($this->_selectMock));
        $this->_adapterMock->expects($this->once())
            ->method('getTransactionLevel')
            ->with()
            ->will($this->returnValue(1));

        // Resources mock
        $this->_resourceMock = $this->_makeMock('Magento\Core\Model\Resource');
        // Resources stubs
        $stubReturnMap = array(
            array('core_read', $this->_adapterMock),
            array('core_write', $this->_adapterMock),
        );
        $this->_resourceMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValueMap($stubReturnMap));
        $this->_resourceMock->expects($this->once())
            ->method('getTableName')
            ->with('outbound_endpoint')
            ->will($this->returnValue(self::TABLE_NAME));

        $this->_endpoint = new \Magento\Webhook\Model\Resource\Endpoint($this->_resourceMock);
    }

    public function testGetApiUserEndpoints()
    {
        $endpoints = array('endpoint1', 'endpoint2', 'endpoint3');

        $this->_selectMock->expects($this->once())
            ->method('where')
            ->with('api_user_id IN (?)', $this->_apiUserIds)
            ->will($this->returnSelf());

        $this->_adapterMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->_selectMock)
            ->will($this->returnValue($endpoints));

        $this->assertEquals($endpoints, $this->_endpoint->getApiUserEndpoints($this->_apiUserIds));
    }

    public function testGetEndpointsWithoutApiUser()
    {
        $endpoints = array('endpoint1', 'endpoint2', 'endpoint3');

        $this->_selectMock->expects($this->once())
            ->method('where')
            ->with('api_user_id IS NULL')
            ->will($this->returnSelf());

        $this->_adapterMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->_selectMock)
            ->will($this->returnValue($endpoints));

        $this->assertEquals($endpoints, $this->_endpoint->getEndpointsWithoutApiUser());
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
