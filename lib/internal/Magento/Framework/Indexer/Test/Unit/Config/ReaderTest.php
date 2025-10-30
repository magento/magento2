<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Test\Unit\Config;

use Magento\Framework\App\Config\FileResolver;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\Indexer\Config\Converter;
use Magento\Framework\Indexer\Config\Reader;
use Magento\Framework\Indexer\Config\SchemaLocator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var Reader
     */
    protected $_model;

    /**
     * @var Converter|MockObject
     */
    protected $_converter;

    /**
     * @var FileResolver|MockObject
     */
    protected $_fileResolverMock;

    protected function setUp(): void
    {
        $this->_fileResolverMock = $this->createPartialMock(FileResolver::class, ['get']);

        $this->_converter = $this->createPartialMock(Converter::class, ['convert']);
        $validationState = $this->getMockForAbstractClass(ValidationStateInterface::class);
        $validationState->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(false);

        $this->_model = new Reader(
            $this->_fileResolverMock,
            $this->_converter,
            new SchemaLocator(
                new UrnResolver()
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
                Assert::assertXmlStringEqualsXmlString($expected, $actual->saveXML());
                return true;
            } catch (AssertionFailedError $e) {
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
    public static function readerDataProvider()
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
