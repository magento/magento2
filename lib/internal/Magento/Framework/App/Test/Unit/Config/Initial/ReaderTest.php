<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Config\Initial;

use Magento\Framework\Filesystem;

class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Config\Initial\Reader
     */
    protected $model;

    /**
     * @var \Magento\Framework\Config\FileResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileResolverMock;

    /**
     * @var \Magento\Framework\App\Config\Initial\Converter|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $converterMock;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var \Magento\Framework\Config\ValidationStateInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $validationStateMock;

    /**
     * @var \Magento\Framework\App\Config\Initial\SchemaLocator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $schemaLocatorMock;

    /**
     * @var \Magento\Framework\Config\DomFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $domFactoryMock;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->filePath = __DIR__ . '/_files/';
        $this->fileResolverMock = $this->createMock(\Magento\Framework\Config\FileResolverInterface::class);
        $this->converterMock = $this->createMock(\Magento\Framework\App\Config\Initial\Converter::class);
        $this->schemaLocatorMock = $this->createMock(\Magento\Framework\App\Config\Initial\SchemaLocator::class);
        $this->validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $this->validationStateMock->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(true);
        $this->domFactoryMock = $this->createMock(\Magento\Framework\Config\DomFactory::class);
    }

    public function testConstructor()
    {
        $this->createModelAndVerifyConstructor();
    }

    /**
     * @covers \Magento\Framework\App\Config\Initial\Reader::read
     */
    public function testReadNoFiles()
    {
        $this->createModelAndVerifyConstructor();
        $this->fileResolverMock->expects($this->at(0))
            ->method('get')
            ->with('config.xml', 'global')
            ->willReturn([]);

        $this->assertEquals([], $this->model->read());
    }

    /**
     * @covers \Magento\Framework\App\Config\Initial\Reader::read
     */
    public function testReadValidConfig()
    {
        $this->createModelAndVerifyConstructor();
        $this->prepareDomFactoryMock();
        $testXmlFilesList = [
            file_get_contents($this->filePath . 'initial_config1.xml'),
            file_get_contents($this->filePath . 'initial_config2.xml'),
        ];
        $expectedConfig = ['data' => [], 'metadata' => []];

        $this->fileResolverMock->expects($this->at(0))
            ->method('get')
            ->with('config.xml', 'global')
            ->willReturn($testXmlFilesList);

        $this->converterMock->expects($this->once())
            ->method('convert')
            ->with($this->anything())
            ->willReturn($expectedConfig);

        $this->assertEquals($expectedConfig, $this->model->read());
    }

    private function prepareDomFactoryMock()
    {
        $validationStateMock = $this->validationStateMock;
        $this->domFactoryMock->expects($this->once())
            ->method('createDom')
            ->willReturnCallback(
                function ($arguments) use ($validationStateMock) {
                    return new \Magento\Framework\Config\Dom(
                        $arguments['xml'],
                        $validationStateMock,
                        [],
                        null,
                        $arguments['schemaFile']
                    );
                }
            );
    }

    /**
     * @covers \Magento\Framework\App\Config\Initial\Reader::read
     */
    public function testReadInvalidConfig()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Verify the XML and try again.');

        $this->createModelAndVerifyConstructor();
        $this->prepareDomFactoryMock();
        $testXmlFilesList = [
            file_get_contents($this->filePath . 'invalid_config.xml'),
            file_get_contents($this->filePath . 'initial_config2.xml'),
        ];
        $expectedConfig = ['data' => [], 'metadata' => []];

        $this->fileResolverMock->expects($this->at(0))
            ->method('get')
            ->with('config.xml', 'global')
            ->willReturn($testXmlFilesList);

        $this->converterMock->expects($this->never())
            ->method('convert')
            ->with($this->anything())
            ->willReturn($expectedConfig);

        $this->model->read();
    }

    private function createModelAndVerifyConstructor()
    {
        $schemaFile = $this->filePath . 'config.xsd';
        $this->schemaLocatorMock->expects($this->once())->method('getSchema')->willReturn($schemaFile);
        $this->model = $this->objectManager->getObject(
            \Magento\Framework\App\Config\Initial\Reader::class,
            [
                'fileResolver' => $this->fileResolverMock,
                'converter' => $this->converterMock,
                'schemaLocator' => $this->schemaLocatorMock,
                'domFactory' => $this->domFactoryMock
            ]
        );
    }
}
