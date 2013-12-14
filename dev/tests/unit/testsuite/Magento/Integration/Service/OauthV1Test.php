<?php
/**
 * Test for \Magento\Integration\Service\OauthV1
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

namespace Magento\Integration\Service;

use Magento\Integration\Model\Integration;

class OauthV1Test extends \PHPUnit_Framework_TestCase
{
    const VALUE_CONSUMER_ID = 1;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_consumerFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $_consumerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $_emptyConsumerMock;

    /** @var \Magento\Integration\Service\OauthV1 */
    private $_service;

    /** @var array */
    private $_consumerData;

    protected function setUp()
    {
        $this->_consumerFactory = $this->getMockBuilder('Magento\Integration\Model\Oauth\Consumer\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_consumerMock = $this->getMockBuilder('Magento\Integration\Model\Oauth\Consumer')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getData',
                    'getId',
                    'load',
                    'save',
                    'delete',
                    '__wakeup'
                ]
            )
            ->getMock();
        $this->_consumerData = array(
            'entity_id' => self::VALUE_CONSUMER_ID,
            'key' => 'jhgjhgjgjiyuiuyuyhhhjkjlklkj',
            'secret' => 'iuyytrfdsdfbnnhbmkkjlkjl',
            'created_at' => '',
            'updated_at' => '',
            'callback_url' => '',
            'rejected_callback_url' => ''
        );
        $this->_consumerFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_consumerMock));

        $this->_service = new \Magento\Integration\Service\OauthV1(
            $this->getMock('Magento\Core\Model\StoreManagerInterface', [], [], '', false),
            $this->_consumerFactory,
            $this->getMock('Magento\Integration\Model\Oauth\Token\Factory', [], [], '', false),
            $this->getMock('Magento\Integration\Helper\Oauth\Data', [], [], '', false),
            $this->getMock('Magento\HTTP\ZendClient', [], [], '', false),
            $this->getMock('Magento\Logger', [], [], '', false),
            $this->getMock('Magento\Oauth\Helper\Oauth', [], [], '', false),
            $this->getMock('Magento\Integration\Model\Oauth\Token\Provider', [], [], '', false)
        );
        $this->_emptyConsumerMock = $this->getMockBuilder('Magento\Integration\Model\Integration')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getData',
                    'load',
                    'getId',
                    'save',
                    'delete',
                    '__wakeup'
                ]
            )
            ->getMock();
        $this->_emptyConsumerMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));
    }

    public function testDelete()
    {
        $this->_consumerMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(self::VALUE_CONSUMER_ID));
        $this->_consumerMock->expects($this->once())
            ->method('load')
            ->with(self::VALUE_CONSUMER_ID)
            ->will($this->returnValue($this->_consumerMock));
        $this->_consumerMock->expects($this->once())
            ->method('delete')
            ->will($this->returnValue($this->_consumerMock));
        $this->_consumerMock->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($this->_consumerData));
        $consumerData = $this->_service->deleteConsumer(self::VALUE_CONSUMER_ID);
        $this->assertEquals($this->_consumerData['entity_id'], $consumerData['entity_id']);
    }

    /**
     * @expectedException \Magento\Integration\Exception
     * @expectedExceptionMessage Consumer with ID '1' does not exist.
     */
    public function testDeleteException()
    {
        $this->_consumerMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));
        $this->_consumerMock->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $this->_consumerMock->expects($this->never())
            ->method('delete');
        $this->_service->deleteConsumer(self::VALUE_CONSUMER_ID);
    }
}
