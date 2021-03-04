<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ResourceConnection\Config;

class ReaderTest extends \PHPUnit\Framework\TestCase
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
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_fileResolverMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_converterMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_schemaLocatorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_configLocalMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_validationStateMock;

    protected function setUp(): void
    {
        $this->_filePath = __DIR__ . '/_files/';

        $this->_fileResolverMock = $this->createMock(\Magento\Framework\Config\FileResolverInterface::class);
        $this->_validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $this->_schemaLocatorMock =
            $this->createMock(\Magento\Framework\App\ResourceConnection\Config\SchemaLocator::class);

        $this->_converterMock =
            $this->createMock(\Magento\Framework\App\ResourceConnection\Config\Converter::class);

        $this->_configLocalMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);

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
        )->willReturn(
            [file_get_contents($this->_filePath . 'resources.xml')]
        );

        $this->_converterMock->expects($this->once())->method('convert')->willReturn($modulesConfig);

        $this->assertEquals($expectedResult, $this->_model->read());
    }
}
