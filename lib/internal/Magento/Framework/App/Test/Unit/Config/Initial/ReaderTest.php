<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config\Initial;

use Magento\Framework\App\Config\Initial\Converter;
use Magento\Framework\App\Config\Initial\Reader;
use Magento\Framework\App\Config\Initial\SchemaLocator;
use Magento\Framework\Config\Dom;
use Magento\Framework\Config\DomFactory;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Reader
     */
    protected $model;

    /**
     * @var FileResolverInterface|MockObject
     */
    protected $fileResolverMock;

    /**
     * @var Converter|MockObject
     */
    protected $converterMock;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var ValidationStateInterface|MockObject
     */
    protected $validationStateMock;

    /**
     * @var SchemaLocator|MockObject
     */
    protected $schemaLocatorMock;

    /**
     * @var DomFactory|MockObject
     */
    protected $domFactoryMock;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->objectManager = new ObjectManager($this);
        $this->filePath = __DIR__ . '/_files/';
        $this->fileResolverMock = $this->getMockForAbstractClass(FileResolverInterface::class);
        $this->converterMock = $this->createMock(Converter::class);
        $this->schemaLocatorMock = $this->createMock(SchemaLocator::class);
        $this->validationStateMock = $this->getMockForAbstractClass(ValidationStateInterface::class);
        $this->validationStateMock->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(true);
        $this->domFactoryMock = $this->createMock(DomFactory::class);
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
                    return new Dom(
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
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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
            Reader::class,
            [
                'fileResolver' => $this->fileResolverMock,
                'converter' => $this->converterMock,
                'schemaLocator' => $this->schemaLocatorMock,
                'domFactory' => $this->domFactoryMock
            ]
        );
    }
}
