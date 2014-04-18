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
namespace Magento\Framework\App\Config;

class InitialTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\Initial
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_initialReaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configCacheMock;

    protected function setUp()
    {
        $this->_initialReaderMock =
            $this->getMock('Magento\Framework\App\Config\Initial\Reader', array(), array(), '', false);
        $this->_configCacheMock =
            $this->getMock('Magento\Framework\App\Cache\Type\Config', array(), array(), '', false);
        $serializedData = serialize(
            array(
                'data' => array(
                    'default' => array('key' => 'default_value'),
                    'stores' => array('default' => array('key' => 'store_value')),
                    'websites' => array('default' => array('key' => 'website_value'))
                ),
                'metadata' => array('metadata')
            )
        );
        $this->_configCacheMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            'initial_config'
        )->will(
            $this->returnValue($serializedData)
        );

        $this->_model = new \Magento\Framework\App\Config\Initial($this->_initialReaderMock, $this->_configCacheMock);
    }

    /**
     * @dataProvider getDataDataProvider
     *
     * @param string $scope
     * @param array $expectedResult
     */
    public function testGetData($scope, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_model->getData($scope));
    }

    public function getDataDataProvider()
    {
        return array(
            array('default', array('key' => 'default_value')),
            array('stores|default', array('key' => 'store_value')),
            array('websites|default', array('key' => 'website_value'))
        );
    }

    public function testGetMetadata()
    {
        $expectedResult = array('metadata');
        $this->assertEquals($expectedResult, $this->_model->getMetadata());
    }
}
