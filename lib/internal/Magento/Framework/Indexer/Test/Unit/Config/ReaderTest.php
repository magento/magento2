<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Test\Unit\Config;

class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Indexer\Config\Reader
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Indexer\Config\Converter|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_converter;

    /**
     * @var \Magento\Framework\App\Config\FileResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_fileResolverMock;

    protected function setUp(): void
    {
        $this->_fileResolverMock = $this->createPartialMock(\Magento\Framework\App\Config\FileResolver::class, ['get']);

        $this->_converter = $this->createPartialMock(\Magento\Framework\Indexer\Config\Converter::class, ['convert']);
        $validationState = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationState->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(false);

        $this->_model = new \Magento\Framework\Indexer\Config\Reader(
            $this->_fileResolverMock,
            $this->_converter,
            new \Magento\Framework\Indexer\Config\SchemaLocator(
                new \Magento\Framework\Config\Dom\UrnResolver()
            ),
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
        )->willReturn(
            $files
        );

        $constraint = function (\DOMDocument $actual) use ($expectedFile) {
            try {
                $expected = file_get_contents(__DIR__ . '/../_files/' . $expectedFile);
                \PHPUnit\Framework\Assert::assertXmlStringEqualsXmlString($expected, $actual->saveXML());
                return true;
            } catch (\PHPUnit\Framework\AssertionFailedError $e) {
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
        )->willReturn(
            $expectedResult
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
                    'indexer_one.xml' => file_get_contents(__DIR__ . '/../_files/indexer_one.xml'),
                    'indexer_two.xml' => file_get_contents(__DIR__ . '/../_files/indexer_two.xml'),
                ],
                'indexer_merged_one.xml',
            ],
            [
                [
                    'indexer_one.xml' => file_get_contents(__DIR__ . '/../_files/indexer_one.xml'),
                    'indexer_three.xml' => file_get_contents(__DIR__ . '/../_files/indexer_three.xml'),
                ],
                'indexer_merged_two.xml'
            ]
        ];
    }
}
