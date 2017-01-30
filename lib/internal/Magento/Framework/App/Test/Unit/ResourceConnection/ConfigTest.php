<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ResourceConnection;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\\Config
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
            $this->getMock('Magento\Framework\App\ResourceConnection\Config\Reader', [], [], '', false);

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

        $deploymentConfig = $this->getMock('\Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $deploymentConfig->expects($this->once())
            ->method('getConfigData')
            ->with('resource')
            ->willReturn($this->_initialResources);

        $this->_model = new \Magento\Framework\App\ResourceConnection\Config(
            $this->_readerMock,
            $this->_scopeMock,
            $this->_cacheMock,
            $deploymentConfig,
            'cacheId'
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
        $deploymentConfig = $this->getMock('\Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $deploymentConfig->expects($this->once())
            ->method('getConfigData')
            ->with('resource')
            ->willReturn(['validResource' => ['somekey' => 'validConnectionName']]);

        new \Magento\Framework\App\ResourceConnection\Config(
            $this->_readerMock,
            $this->_scopeMock,
            $this->_cacheMock,
            $deploymentConfig,
            'cacheId'
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
                'connectionName' => \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
            ],
            ['resourceName' => 'extendedResourceName', 'connectionName' => 'validConnectionName'],
            ['resourceName' => 'validResource', 'connectionName' => 'validConnectionName']
        ];
    }
}
