<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ResourceConnection;

use Magento\Framework\Config\ConfigOptionsListConstants;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ResourceConnection\Config
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
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

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

        $this->_cacheMock->expects(
            $this->any()
        )->method(
            'load'
        )->will(
            $this->returnValue(serialize($this->_resourcesConfig))
        );

        $this->deploymentConfig = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
        $this->_model = new \Magento\Framework\App\ResourceConnection\Config(
            $this->_readerMock,
            $this->_scopeMock,
            $this->_cacheMock,
            $this->deploymentConfig,
            'cacheId'
        );

        $this->_model = new \Magento\Framework\App\ResourceConnection\Config(
            $this->_readerMock,
            $this->_scopeMock,
            $this->_cacheMock,
            $this->deploymentConfig,
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
        $this->deploymentConfig->expects($this->once())
            ->method('getConfigData')
            ->with(ConfigOptionsListConstants::KEY_RESOURCE)
            ->willReturn([
                'validResource' => ['connection' => 'validConnectionName'],
            ]);
        $this->assertEquals($connectionName, $this->_model->getConnectionName($resourceName));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetConnectionNameWithException()
    {
        $deploymentConfig = $this->getMock('\Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $deploymentConfig->expects($this->once())
            ->method('getConfigData')
            ->with(ConfigOptionsListConstants::KEY_RESOURCE)
            ->willReturn(['validResource' => ['somekey' => 'validConnectionName']]);

        $model = new \Magento\Framework\App\ResourceConnection\Config(
            $this->_readerMock,
            $this->_scopeMock,
            $this->_cacheMock,
            $deploymentConfig,
            'cacheId'
        );
        $model->getConnectionName('default');
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
