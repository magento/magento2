<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Address\Config;

class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\Address\Config\Reader
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Config\FileResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_fileResolverMock;

    /**
     * @var \Magento\Customer\Model\Address\Config\Converter|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_converter;

    /**
     * @var \Magento\Customer\Model\Address\Config\SchemaLocator
     */
    protected $_schemaLocator;

    /**
     * @var \Magento\Framework\Config\ValidationStateInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_validationState;

    protected function setUp(): void
    {
        $this->_fileResolverMock = $this->createMock(\Magento\Framework\Config\FileResolverInterface::class);
        $this->_fileResolverMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'address_formats.xml',
            'scope'
        )->willReturn(
            
                [
                    file_get_contents(__DIR__ . '/_files/formats_one.xml'),
                    file_get_contents(__DIR__ . '/_files/formats_two.xml'),
                ]
            
        );

        $this->_converter = $this->createPartialMock(
            \Magento\Customer\Model\Address\Config\Converter::class,
            ['convert']
        );

        $moduleReader = $this->createPartialMock(\Magento\Framework\Module\Dir\Reader::class, ['getModuleDir']);

        $moduleReader->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Customer'
        )->willReturn(
            'stub'
        );

        $this->_schemaLocator = new \Magento\Customer\Model\Address\Config\SchemaLocator($moduleReader);
        $this->_validationState = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $this->_validationState->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(false);

        $this->_model = new \Magento\Customer\Model\Address\Config\Reader(
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
                $expected = __DIR__ . '/_files/formats_merged.xml';
                \PHPUnit\Framework\Assert::assertXmlStringEqualsXmlFile($expected, $actual->saveXML());
                return true;
            } catch (\PHPUnit\Framework\AssertionFailedError $e) {
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
