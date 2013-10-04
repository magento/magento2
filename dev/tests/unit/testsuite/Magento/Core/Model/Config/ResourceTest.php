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
namespace Magento\Core\Model\Config;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Config\Resource
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    /**
     * @var array
     */
    protected $_resourcesConfig;

    protected function setUp()
    {
        $this->_scopeMock = $this->getMock('Magento\Config\ScopeInterface');
        $this->_cacheMock = $this->getMock('Magento\Config\CacheInterface');

        $this->_readerMock = $this->getMock(
            'Magento\Core\Model\Resource\Config\Reader', array(), array(), '', false
        );

        $this->_resourcesConfig = array(
            'mainResourceName' => array(
                'name' => 'mainResourceName',
                'extends' => 'anotherResourceName',
            ),
            'otherResourceName' => array(
                'name' => 'otherResourceName',
                'connection' => 'otherConnectionName',
            ),
            'anotherResourceName' => array(
                'name' => 'anotherResourceName',
                'connection' => 'anotherConnection'
            ),
            'brokenResourceName' => array(
                'name' => 'brokenResourceName',
                'extends' => 'absentResourceName',
            ),
        );

        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue(serialize($this->_resourcesConfig)));

        $this->_model = new \Magento\Core\Model\Config\Resource(
            $this->_readerMock,
            $this->_scopeMock,
            $this->_cacheMock,
            'cacheId'
        );
    }

    /**
     * @covers \Magento\Core\Model\Config\Resource::getConnectionName
     * @dataProvider getConnectionNameDataProvider
     * @param string $resourceName
     * @param string $connectionName
     */
    public function testGetConnectionName($resourceName, $connectionName)
    {
        $this->assertEquals($connectionName, $this->_model->getConnectionName($resourceName));
    }

    /**
     * @return array
     */
    public function getConnectionNameDataProvider()
    {
        return array(
            array(
                'resourceName' => 'otherResourceName',
                'connectionName' => 'otherConnectionName',
            ),
            array(
                'resourceName' => 'mainResourceName',
                'connectionName' => 'anotherConnection',
            ),
            array(
                'resourceName' => 'brokenResourceName',
                'connectionName' => \Magento\Core\Model\Config\Resource::DEFAULT_SETUP_CONNECTION,
            )
        );
    }
}
