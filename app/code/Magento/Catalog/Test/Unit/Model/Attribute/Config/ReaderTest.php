<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Attribute\Config;

use Magento\Catalog\Model\Attribute\Config\Converter;
use Magento\Catalog\Model\Attribute\Config\Reader;
use Magento\Catalog\Model\Attribute\Config\SchemaLocator;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;
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
            'catalog_attributes.xml',
            'scope'
        )->willReturn(
            [
                file_get_contents(__DIR__ . '/_files/attributes_config_one.xml'),
                file_get_contents(__DIR__ . '/_files/attributes_config_two.xml'),
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
            'Magento_Catalog'
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
            $this->_validationState
        );
    }

    public function testRead()
    {
        $expectedResult = new \stdClass();
        $constraint = function (\DOMDocument $actual) {
            try {
                $expected = __DIR__ . '/_files/attributes_config_merged.xml';
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
