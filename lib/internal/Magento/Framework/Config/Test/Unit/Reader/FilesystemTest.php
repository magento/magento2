<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit\Reader;

use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Config\ValidationStateInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for
 *
 * @see Filesystem
 */
class FilesystemTest extends TestCase
{
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
    protected $_validationStateMock;

    /**
     * @var UrnResolver
     */
    protected $urnResolver;

    /**
     * @var string
     */
    protected $_file;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->_file = file_get_contents(__DIR__ . '/../_files/reader/config.xml');
        $this->_fileResolverMock = $this->getMockForAbstractClass(FileResolverInterface::class);
        $this->_converterMock = $this->getMockForAbstractClass(ConverterInterface::class);
        $this->_schemaLocatorMock = $this->getMockForAbstractClass(SchemaLocatorInterface::class);
        $this->_validationStateMock = $this->getMockForAbstractClass(ValidationStateInterface::class);
        $this->urnResolver = new UrnResolver();
    }

    public function testRead()
    {
        $model = new Filesystem(
            $this->_fileResolverMock,
            $this->_converterMock,
            $this->_schemaLocatorMock,
            $this->_validationStateMock,
            'fileName',
            []
        );
        $this->_fileResolverMock->expects($this->once())->method('get')->willReturn([$this->_file]);

        $dom = new \DOMDocument();
        $dom->loadXML($this->_file);
        $this->_converterMock->expects($this->once())->method('convert')->with($dom);
        $model->read('scope');
    }

    public function testReadWithoutFiles()
    {
        $model = new Filesystem(
            $this->_fileResolverMock,
            $this->_converterMock,
            $this->_schemaLocatorMock,
            $this->_validationStateMock,
            'fileName',
            []
        );
        $this->_fileResolverMock
            ->expects($this->once())->method('get')->willReturn([]);

        $this->assertEmpty($model->read('scope'));
    }

    public function testReadWithInvalidDom()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid Document');
        $this->_schemaLocatorMock->expects(
            $this->once()
        )->method(
            'getSchema'
        )->willReturn(
            $this->urnResolver->getRealPath('urn:magento:framework:Config/Test/Unit/_files/reader/schema.xsd')
        );
        $this->_validationStateMock->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(true);
        $model = new Filesystem(
            $this->_fileResolverMock,
            $this->_converterMock,
            $this->_schemaLocatorMock,
            $this->_validationStateMock,
            'fileName',
            []
        );
        $this->_fileResolverMock->expects($this->once())->method('get')->willReturn([$this->_file]);

        $model->read('scope');
    }

    public function testReadWithInvalidXml()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The XML in file "0" is invalid:');
        $this->_schemaLocatorMock->expects(
            $this->any()
        )->method(
            'getPerFileSchema'
        )->willReturn(
            $this->urnResolver->getRealPath('urn:magento:framework:Config/Test/Unit/_files/reader/schema.xsd')
        );
        $this->_validationStateMock->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(true);

        $model = new Filesystem(
            $this->_fileResolverMock,
            $this->_converterMock,
            $this->_schemaLocatorMock,
            $this->_validationStateMock,
            'fileName',
            []
        );
        $this->_fileResolverMock->expects($this->once())->method('get')->willReturn([$this->_file]);
        $model->read('scope');
    }

    public function testReadException()
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Instance of the DOM config merger is expected, got StdClass instead.');
        $this->_fileResolverMock->expects($this->once())->method('get')->willReturn([$this->_file]);
        $model = new Filesystem(
            $this->_fileResolverMock,
            $this->_converterMock,
            $this->_schemaLocatorMock,
            $this->_validationStateMock,
            'fileName',
            [],
            'StdClass'
        );
        $model->read();
    }
}
