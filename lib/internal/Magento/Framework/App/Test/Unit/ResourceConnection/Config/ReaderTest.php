<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ResourceConnection\Config;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ResourceConnection\Config\Reader
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_filePath;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_schemaLocatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configLocalMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_validationStateMock;

    protected function setUp()
    {
        $this->_filePath = __DIR__ . '/_files/';

        $this->_fileResolverMock = $this->getMock(\Magento\Framework\Config\FileResolverInterface::class);
        $this->_validationStateMock = $this->getMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $this->_schemaLocatorMock = $this->getMock(
            \Magento\Framework\App\ResourceConnection\Config\SchemaLocator::class,
            [],
            [],
            '',
            false
        );

        $this->_converterMock =
            $this->getMock(\Magento\Framework\App\ResourceConnection\Config\Converter::class, [], [], '', false);

        $this->_configLocalMock = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);

        $this->_model = new \Magento\Framework\App\ResourceConnection\Config\Reader(
            $this->_fileResolverMock,
            $this->_converterMock,
            $this->_schemaLocatorMock,
            $this->_validationStateMock,
            $this->_configLocalMock
        );
    }

    public function testRead()
    {
        $modulesConfig = include $this->_filePath . 'resources.php';

        $expectedResult = [
            'resourceName' => ['name' => 'resourceName', 'extends' => 'anotherResourceName'],
            'otherResourceName' => ['name' => 'otherResourceName', 'connection' => 'connectionName'],
            'defaultSetup' => ['name' => 'defaultSetup', 'connection' => 'customConnection'],
        ];

        $this->_fileResolverMock->expects(
            $this->once()
        )->method(
            'get'
        )->will(
            $this->returnValue([file_get_contents($this->_filePath . 'resources.xml')])
        );

        $this->_converterMock->expects($this->once())->method('convert')->will($this->returnValue($modulesConfig));

        $this->assertEquals($expectedResult, $this->_model->read());
    }
}
