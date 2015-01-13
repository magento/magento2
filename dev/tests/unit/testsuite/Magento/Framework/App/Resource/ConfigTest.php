<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Resource;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Resource\Config
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

    /**
     * @var array
     */
    protected $_initialResources;

    protected function setUp()
    {
        $this->_scopeMock = $this->getMock('Magento\Framework\Config\ScopeInterface');
        $this->_cacheMock = $this->getMock('Magento\Framework\Config\CacheInterface');

        $this->_readerMock =
            $this->getMock('Magento\Framework\App\Resource\Config\Reader', [], [], '', false);

        $this->_resourcesConfig = [
            'mainResourceName' => ['name' => 'mainResourceName', 'extends' => 'anotherResourceName'],
            'otherResourceName' => ['name' => 'otherResourceName', 'connection' => 'otherConnectionName'],
            'anotherResourceName' => ['name' => 'anotherResourceName', 'connection' => 'anotherConnection'],
            'brokenResourceName' => ['name' => 'brokenResourceName', 'extends' => 'absentResourceName'],
            'extendedResourceName' => ['name' => 'extendedResourceName', 'extends' => 'validResource'],
        ];

        $this->_initialResources = [
            'validResource' => ['connection' => 'validConnectionName'],
        ];

        $this->_cacheMock->expects(
            $this->any()
        )->method(
            'load'
        )->will(
            $this->returnValue(serialize($this->_resourcesConfig))
        );

        $this->_model = new \Magento\Framework\App\Resource\Config(
            $this->_readerMock,
            $this->_scopeMock,
            $this->_cacheMock,
            'cacheId',
            $this->_initialResources
        );
    }

    /**
     * @dataProvider getConnectionNameDataProvider
     * @param string $resourceName
     * @param string $connectionName
     */
    public function testGetConnectionName($resourceName, $connectionName)
    {
        $this->assertEquals($connectionName, $this->_model->getConnectionName($resourceName));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionConstructor()
    {
        new \Magento\Framework\App\Resource\Config(
            $this->_readerMock,
            $this->_scopeMock,
            $this->_cacheMock,
            'cacheId',
            ['validResource' => ['somekey' => 'validConnectionName']]
        );
    }

    /**
     * @return array
     */
    public function getConnectionNameDataProvider()
    {
        return [
            ['resourceName' => 'otherResourceName', 'connectionName' => 'otherConnectionName'],
            ['resourceName' => 'mainResourceName', 'connectionName' => 'anotherConnection'],
            [
                'resourceName' => 'brokenResourceName',
                'connectionName' => \Magento\Framework\App\Resource\Config::DEFAULT_SETUP_CONNECTION
            ],
            ['resourceName' => 'extendedResourceName', 'connectionName' => 'default'],
            ['resourceName' => 'validResource', 'connectionName' => 'validConnectionName']
        ];
    }
}
