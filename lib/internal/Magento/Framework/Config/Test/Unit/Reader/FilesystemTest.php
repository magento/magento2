<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit\Reader;

use \Magento\Framework\Config\Reader\Filesystem;

class FilesystemTest extends \PHPUnit\Framework\TestCase
{
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
    protected $_validationStateMock;

    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     */
    protected $urnResolver;

    /**
     * @var string
     */
    protected $_file;

    protected function setUp()
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->_file = file_get_contents(__DIR__ . '/../_files/reader/config.xml');
        $this->_fileResolverMock = $this->createMock(\Magento\Framework\Config\FileResolverInterface::class);
        $this->_converterMock = $this->createMock(\Magento\Framework\Config\ConverterInterface::class);
        $this->_schemaLocatorMock = $this->createMock(\Magento\Framework\Config\SchemaLocatorInterface::class);
        $this->_validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
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
        $this->_fileResolverMock->expects($this->once())->method('get')->will($this->returnValue([$this->_file]));

        $dom = new \DomDocument();
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
            ->expects($this->once())->method('get')->will($this->returnValue([]));

        $this->assertEmpty($model->read('scope'));
    }

    /**
     */
    public function testReadWithInvalidDom()
    {
        $this->setExpectedException(\Magento\Framework\Exception\LocalizedException::class, 'Invalid Document');

        $this->_schemaLocatorMock->expects(
            $this->once()
        )->method(
            'getSchema'
        )->will(
            $this->returnValue(
                $this->urnResolver->getRealPath('urn:magento:framework:Config/Test/Unit/_files/reader/schema.xsd')
            )
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
        $this->_fileResolverMock->expects($this->once())->method('get')->will($this->returnValue([$this->_file]));

        $model->read('scope');
    }

    /**
     */
    public function testReadWithInvalidXml()
    {
        $this->setExpectedException(\Magento\Framework\Exception\LocalizedException::class, 'The XML in file "0" is invalid:');

        $this->_schemaLocatorMock->expects(
            $this->any()
        )->method(
            'getPerFileSchema'
        )->will(
            $this->returnValue(
                $this->urnResolver->getRealPath('urn:magento:framework:Config/Test/Unit/_files/reader/schema.xsd')
            )
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
        $this->_fileResolverMock->expects($this->once())->method('get')->will($this->returnValue([$this->_file]));
        $model->read('scope');
    }

    /**
     */
    public function testReadException()
    {
        $this->setExpectedException(\UnexpectedValueException::class, 'Instance of the DOM config merger is expected, got StdClass instead.');

        $this->_fileResolverMock->expects($this->once())->method('get')->will($this->returnValue([$this->_file]));
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
