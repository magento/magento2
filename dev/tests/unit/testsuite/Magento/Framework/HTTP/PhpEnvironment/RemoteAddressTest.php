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
namespace Magento\Framework\HTTP\PhpEnvironment;

class RemoteAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_request = $this->getMockBuilder(
            'Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->setMethods(
            array('getServer')
        )->getMock();

        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @dataProvider getRemoteAddressProvider
     */
    public function testGetRemoteAddress($alternativeHeaders, $serverValueMap, $expected, $ipToLong)
    {
        $remoteAddress = $this->_objectManager->getObject(
            'Magento\Framework\HTTP\PhpEnvironment\RemoteAddress',
            array('httpRequest' => $this->_request, 'alternativeHeaders' => $alternativeHeaders)
        );
        $this->_request->expects($this->any())->method('getServer')->will($this->returnValueMap($serverValueMap));
        $this->assertEquals($expected, $remoteAddress->getRemoteAddress($ipToLong));
    }

    /**
     * @return array
     */
    public function getRemoteAddressProvider()
    {
        return array(
            array(
                'alternativeHeaders' => array(),
                'serverValueMap' => array(array('REMOTE_ADDR', null, null)),
                'expected' => false,
                'ipToLong' => false
            ),
            array(
                'alternativeHeaders' => array(),
                'serverValueMap' => array(array('REMOTE_ADDR', null, '192.168.0.1')),
                'expected' => '192.168.0.1',
                'ipToLong' => false
            ),
            array(
                'alternativeHeaders' => array(),
                'serverValueMap' => array(array('REMOTE_ADDR', null, '192.168.1.1')),
                'expected' => ip2long('192.168.1.1'),
                'ipToLong' => true
            ),
            array(
                'alternativeHeaders' => array('TEST_HEADER'),
                'serverValueMap' => array(
                    array('REMOTE_ADDR', null, '192.168.1.1'),
                    array('TEST_HEADER', null, '192.168.0.1'),
                    array('TEST_HEADER', false, '192.168.0.1')
                ),
                'expected' => '192.168.0.1',
                'ipToLong' => false
            ),
            array(
                'alternativeHeaders' => array('TEST_HEADER'),
                'serverValueMap' => array(
                    array('REMOTE_ADDR', null, '192.168.1.1'),
                    array('TEST_HEADER', null, '192.168.0.1'),
                    array('TEST_HEADER', false, '192.168.0.1')
                ),
                'expected' => ip2long('192.168.0.1'),
                'ipToLong' => true
            )
        );
    }
}
