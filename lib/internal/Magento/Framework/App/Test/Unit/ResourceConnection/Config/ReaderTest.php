<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\ResourceConnection\Config;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection\Config\Converter;
use Magento\Framework\App\ResourceConnection\Config\Reader;
use Magento\Framework\App\ResourceConnection\Config\SchemaLocator;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var Reader
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_filePath;

    /**
     * @var MockObject
     */
    protected $_fileResolverMock;

    /**
     * @var MockObject
     */
    protected $_converterMock;

    /**
     * @var MockObject
     */
    protected $_schemaLocatorMock;

    /**
     * @var MockObject
     */
    protected $_configLocalMock;

    /**
     * @var MockObject
     */
    protected $_validationStateMock;

    protected function setUp(): void
    {
        $this->_filePath = __DIR__ . '/_files/';

        $this->_fileResolverMock = $this->getMockForAbstractClass(FileResolverInterface::class);
        $this->_validationStateMock = $this->getMockForAbstractClass(ValidationStateInterface::class);
        $this->_schemaLocatorMock =
            $this->createMock(SchemaLocator::class);

        $this->_converterMock =
            $this->createMock(Converter::class);

        $this->_configLocalMock = $this->createMock(DeploymentConfig::class);

        $this->_model = new Reader(
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
