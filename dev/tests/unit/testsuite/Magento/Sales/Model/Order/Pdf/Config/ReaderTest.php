<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Pdf\Config;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Pdf\Config\Reader
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Config\FileResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileResolverMock;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Config\Converter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converter;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Config\SchemaLocator
     */
    protected $_schemaLocator;

    /**
     * @var \Magento\Framework\Config\ValidationStateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_validationState;

    protected function setUp()
    {
        $this->_fileResolverMock = $this->getMock('Magento\Framework\Config\FileResolverInterface');
        $this->_fileResolverMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'pdf.xml',
            'scope'
        )->will(
            $this->returnValue(
                [
                    file_get_contents(__DIR__ . '/_files/pdf_one.xml'),
                    file_get_contents(__DIR__ . '/_files/pdf_two.xml'),
                ]
            )
        );

        $this->_converter = $this->getMock('Magento\Sales\Model\Order\Pdf\Config\Converter', ['convert']);

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
            'Magento_Sales'
        )->will(
            $this->returnValue('stub')
        );

        $this->_schemaLocator = new \Magento\Sales\Model\Order\Pdf\Config\SchemaLocator($moduleReader);
        $this->_validationState = $this->getMock('Magento\Framework\Config\ValidationStateInterface');
        $this->_validationState->expects($this->once())->method('isValidated')->will($this->returnValue(false));

        $this->_model = new \Magento\Sales\Model\Order\Pdf\Config\Reader(
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
        $constraint = function (\DOMDOcument $actual) {
            try {
                $expected = __DIR__ . '/_files/pdf_merged.xml';
                \PHPUnit_Framework_Assert::assertXmlStringEqualsXmlFile($expected, $actual->saveXML());
                return true;
            } catch (\PHPUnit_Framework_AssertionFailedError $e) {
                return false;
            }
        };

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
}
