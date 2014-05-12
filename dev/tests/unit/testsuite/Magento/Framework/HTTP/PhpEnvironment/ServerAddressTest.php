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

class ServerAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\ServerAddress
     */
    protected $_serverAddress;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Request\Http
     */
    protected $_request;

    protected function setUp()
    {
        $this->_request = $this->getMockBuilder(
            'Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->setMethods(
            array('getServer')
        )->getMock();

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_serverAddress = $objectManager->getObject(
            'Magento\Framework\HTTP\PhpEnvironment\ServerAddress',
            array('httpRequest' => $this->_request)
        );
    }

    /**
     * @dataProvider getServerAddressProvider
     */
    public function testGetServerAddress($serverVar, $expected, $ipToLong)
    {
        $this->_request->expects(
            $this->atLeastOnce()
        )->method(
            'getServer'
        )->with(
            'SERVER_ADDR'
        )->will(
            $this->returnValue($serverVar)
        );
        $this->assertEquals($expected, $this->_serverAddress->getServerAddress($ipToLong));
    }

    /**
     * @return array
     */
    public function getServerAddressProvider()
    {
        return array(
            array(null, false, false),
            array('192.168.0.1', '192.168.0.1', false),
            array('192.168.1.1', ip2long('192.168.1.1'), true)
        );
    }
}
