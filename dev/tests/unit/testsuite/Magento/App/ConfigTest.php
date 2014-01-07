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
namespace Magento\App;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\Config
     */
    protected $_config;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_loaderMock;

    protected function setUp()
    {
        $this->_loaderMock = $this->getMock('Magento\App\Config\Loader', array(), array(), '', false);
        $params = array(
          'connection' => array('default' => array('connection_name')),
          'resource' => array('name' => array('default_setup'))
        );
        $this->_loaderMock->expects($this->any())->method('load')->will($this->returnValue($params));
        $this->_config = new \Magento\App\Config(
            array(),
            $this->_loaderMock
        );
    }

    /**
     * @param string $connectionName
     * @param array|null $connectionDetail
     * @dataProvider getConnectionDataProvider
     */
    public function testGetConnection($connectionDetail, $connectionName)
    {
        $this->assertEquals($connectionDetail, $this->_config->getConnection($connectionName));
    }

    public function getConnectionDataProvider()
    {
        return array(
            'connection_name_exist' => array(array('connection_name'), 'default'),
            'connection_name_not_exist' => array(null, 'new_default')
        );
    }

    public function testGetConnections()
    {
        $this->assertEquals(array('default' => array('connection_name')), $this->_config->getConnections());
    }

    public function testGetResources()
    {
        $this->assertEquals(array('name' => array('default_setup')), $this->_config->getResources());
    }
}
