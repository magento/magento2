<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\Test\Unit\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Mview\Config\Reader
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Mview\Config\Converter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converter;

    /**
     * @var \Magento\Framework\App\Config\FileResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileResolverMock;

    protected function setUp()
    {
        $this->_fileResolverMock = $this->getMock(
            \Magento\Framework\App\Config\FileResolver::class,
            ['get'],
            [],
            '',
            false
        );

        $this->_converter = $this->getMock(\Magento\Framework\Mview\Config\Converter::class, ['convert']);

        $urnResolverMock = $this->getMock(\Magento\Framework\Config\Dom\UrnResolver::class, [], [], '', false);
        $urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:Mview/etc/mview.xsd')
            ->willReturn('test_folder');
        $schemaLocator = new \Magento\Framework\Mview\Config\SchemaLocator($urnResolverMock);

        $validationState = $this->getMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationState->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(false);

        $this->_model = new \Magento\Framework\Mview\Config\Reader(
            $this->_fileResolverMock,
            $this->_converter,
            $schemaLocator,
            $validationState
        );
    }

    /**
     * @dataProvider readerDataProvider
     */
    public function testReadValidConfig($files, $expectedFile)
    {
        $this->_fileResolverMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'mview.xml',
            'scope'
        )->will(
            $this->returnValue($files)
        );

        $constraint = function (\DOMDocument $actual) use ($expectedFile) {
            try {
                $expected = file_get_contents(__DIR__ . '/../_files/' . $expectedFile);
                \PHPUnit_Framework_Assert::assertXmlStringEqualsXmlString($expected, $actual->saveXML());
                return true;
            } catch (\PHPUnit_Framework_AssertionFailedError $e) {
                return false;
            }
        };
        $expectedResult = new \stdClass();
        $this->_converter->expects(
            $this->once()
        )->method(
            'convert'
        )->with(
            $this->callback($constraint)
        )->will(
            $this->returnValue($expectedResult)
        );

        $this->assertSame($expectedResult, $this->_model->read('scope'));
    }

    /**
     * @return array
     */
    public function readerDataProvider()
    {
        return [
            'mview_merged_one' => [
                [
                    'mview_one.xml' => file_get_contents(__DIR__ . '/../_files/mview_one.xml'),
                    'mview_two.xml' => file_get_contents(__DIR__ . '/../_files/mview_two.xml'),
                ],
                'mview_merged_one.xml',
            ],
            'mview_merged_two' => [
                [
                    'mview_one.xml' => file_get_contents(__DIR__ . '/../_files/mview_one.xml'),
                    'mview_three.xml' => file_get_contents(__DIR__ . '/../_files/mview_three.xml'),
                ],
                'mview_merged_two.xml',
            ]
        ];
    }
}
