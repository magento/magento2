<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Reader;

class FilesystemTest extends \PHPUnit_Framework_TestCase
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
     * @var string
     */
    protected $_file;

    protected function setUp()
    {
        $this->_file = file_get_contents(__DIR__ . '/../_files/reader/config.xml');
        $this->_fileResolverMock = $this->getMock('Magento\Framework\Config\FileResolverInterface');
        $this->_converterMock = $this->getMock(
            'Magento\Framework\Config\ConverterInterface',
            [],
            [],
            '',
            false
        );
        $this->_schemaLocatorMock = $this->getMock('Magento\Framework\Config\SchemaLocatorInterface');
        $this->_validationStateMock = $this->getMock('Magento\Framework\Config\ValidationStateInterface');
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
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage Invalid Document
     */
    public function testReadWithInvalidDom()
    {
        $this->_schemaLocatorMock->expects(
            $this->once()
        )->method(
            'getSchema'
        )->will(
            $this->returnValue(__DIR__ . "/../_files/reader/schema.xsd")
        );
        $this->_validationStateMock->expects($this->any())->method('isValidated')->will($this->returnValue(true));
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
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage Invalid XML in file
     */
    public function testReadWithInvalidXml()
    {
        $this->_schemaLocatorMock->expects(
            $this->any()
        )->method(
            'getPerFileSchema'
        )->will(
            $this->returnValue(__DIR__ . "/../_files/reader/schema.xsd")
        );
        $this->_validationStateMock->expects($this->any())->method('isValidated')->will($this->returnValue(true));

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
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Instance of the DOM config merger is expected, got StdClass instead.
     */
    public function testReadException()
    {
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
