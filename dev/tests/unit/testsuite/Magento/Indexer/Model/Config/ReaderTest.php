<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Config;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\Config\Reader
     */
    protected $_model;

    /**
     * @var \Magento\Indexer\Model\Config\Converter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converter;

    /**
     * @var \Magento\Framework\App\Config\FileResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileResolverMock;

    protected function setUp()
    {
        $this->_fileResolverMock = $this->getMock(
            'Magento\Framework\App\Config\FileResolver',
            ['get'],
            [],
            '',
            false
        );

        $this->_converter = $this->getMock('Magento\Indexer\Model\Config\Converter', ['convert']);

        $moduleReader = $this->getMock(
            'Magento\Framework\Module\Dir\Reader',
            ['getModuleDir'],
            [],
            '',
            false
        );
        $moduleReader->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Indexer'
        )->will(
            $this->returnValue('stub')
        );
        $schemaLocator = new \Magento\Indexer\Model\Config\SchemaLocator($moduleReader);

        $validationState = $this->getMock('Magento\Framework\Config\ValidationStateInterface');
        $validationState->expects($this->once())->method('isValidated')->will($this->returnValue(false));

        $this->_model = new \Magento\Indexer\Model\Config\Reader(
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
            'indexer.xml',
            'scope'
        )->will(
            $this->returnValue($files)
        );

        $constraint = function (\DOMDocument $actual) use ($expectedFile) {
            try {
                $expected = file_get_contents(__DIR__ . '/../../_files/' . $expectedFile);
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
            [
                [
                    'indexer_one.xml' => file_get_contents(__DIR__ . '/../../_files/indexer_one.xml'),
                    'indexer_two.xml' => file_get_contents(__DIR__ . '/../../_files/indexer_two.xml'),
                ],
                'indexer_merged_one.xml',
            ],
            [
                [
                    'indexer_one.xml' => file_get_contents(__DIR__ . '/../../_files/indexer_one.xml'),
                    'indexer_three.xml' => file_get_contents(__DIR__ . '/../../_files/indexer_three.xml'),
                ],
                'indexer_merged_two.xml'
            ]
        ];
    }
}
