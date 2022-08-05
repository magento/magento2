<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Pdf\Config;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Sales\Model\Order\Pdf\Config\Converter;
use Magento\Sales\Model\Order\Pdf\Config\Reader;
use Magento\Sales\Model\Order\Pdf\Config\SchemaLocator;
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
     * @var FileResolverInterface|MockObject
     */
    protected $_fileResolverMock;

    /**
     * @var Converter|MockObject
     */
    protected $_converter;

    /**
     * @var SchemaLocator
     */
    protected $_schemaLocator;

    /**
     * @var ValidationStateInterface|MockObject
     */
    protected $_validationState;

    protected function setUp(): void
    {
        $this->_fileResolverMock = $this->getMockForAbstractClass(FileResolverInterface::class);
        $this->_fileResolverMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'pdf.xml',
            'scope'
        )->willReturn(
            [
                file_get_contents(__DIR__ . '/_files/pdf_one.xml'),
                file_get_contents(__DIR__ . '/_files/pdf_two.xml'),
            ]
        );

        $this->_converter = $this->createPartialMock(
            Converter::class,
            ['convert']
        );

        $moduleReader = $this->createPartialMock(\Magento\Framework\Module\Dir\Reader::class, ['getModuleDir']);

        $moduleReader->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Sales'
        )->willReturn(
            'stub'
        );

        $this->_schemaLocator = new SchemaLocator($moduleReader);
        $this->_validationState = $this->getMockForAbstractClass(ValidationStateInterface::class);
        $this->_validationState->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(false);

        $this->_model = new Reader(
            $this->_fileResolverMock,
            $this->_converter,
            $this->_schemaLocator,
            $this->_validationState,
            'pdf.xml'
        );
    }

    public function testRead()
    {
        $expectedResult = new \stdClass();
        $constraint = function (\DOMDocument $actual) {
            try {
                $expected = __DIR__ . '/_files/pdf_merged.xml';
                Assert::assertXmlStringEqualsXmlFile($expected, $actual->saveXML());
                return true;
            } catch (AssertionFailedError $e) {
                return false;
            }
        };

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
}
