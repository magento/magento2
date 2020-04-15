<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model\Template\Config;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Email\Model\Template\Config\Reader
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Attribute\Config\Converter|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_converter;

    /**
     * @var \Magento\Framework\Module\Dir\ReverseResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_moduleDirResolver;

    /**
     * @var \Magento\Framework\Filesystem\File\Read|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $read;

    /**
     * Paths to fixtures
     *
     * @var array
     */
    protected $_paths;

    protected function setUp(): void
    {
        $fileResolver = $this->createMock(\Magento\Email\Model\Template\Config\FileResolver::class);
        $this->_paths = [
            __DIR__ . '/_files/Fixture/ModuleOne/etc/email_templates_one.xml',
            __DIR__ . '/_files/Fixture/ModuleTwo/etc/email_templates_two.xml',
        ];

        $this->_converter = $this->createPartialMock(
            \Magento\Email\Model\Template\Config\Converter::class,
            ['convert']
        );

        $moduleReader = $this->createPartialMock(\Magento\Framework\Module\Dir\Reader::class, ['getModuleDir']);
        $moduleReader->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Email'
        )->willReturn(
            'stub'
        );
        $schemaLocator = new \Magento\Email\Model\Template\Config\SchemaLocator($moduleReader);

        $validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationStateMock->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(false);

        $this->_moduleDirResolver = $this->createMock(\Magento\Framework\Module\Dir\ReverseResolver::class);
        $readFactory = $this->createMock(\Magento\Framework\Filesystem\File\ReadFactory::class);
        $this->read = $this->createMock(\Magento\Framework\Filesystem\File\Read::class);
        $readFactory->expects($this->any())->method('create')->willReturn($this->read);

        $fileIterator = new \Magento\Email\Model\Template\Config\FileIterator(
            $readFactory,
            $this->_paths,
            $this->_moduleDirResolver
        );
        $fileResolver->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'email_templates.xml',
            'scope'
        )->willReturn(
            $fileIterator
        );

        $this->_model = new \Magento\Email\Model\Template\Config\Reader(
            $fileResolver,
            $this->_converter,
            $schemaLocator,
            $validationStateMock
        );
    }

    public function testRead()
    {
        $this->read->expects(
            $this->at(0)
        )->method(
            'readAll'
        )->willReturn(
            file_get_contents($this->_paths[0])
        );
        $this->read->expects(
            $this->at(1)
        )->method(
            'readAll'
        )->willReturn(
            file_get_contents($this->_paths[1])
        );
        $this->_moduleDirResolver->expects(
            $this->at(0)
        )->method(
            'getModuleName'
        )->with(
            __DIR__ . '/_files/Fixture/ModuleOne/etc/email_templates_one.xml'
        )->willReturn(
            'Fixture_ModuleOne'
        );
        $this->_moduleDirResolver->expects(
            $this->at(1)
        )->method(
            'getModuleName'
        )->with(
            __DIR__ . '/_files/Fixture/ModuleTwo/etc/email_templates_two.xml'
        )->willReturn(
            'Fixture_ModuleTwo'
        );
        $constraint = function (\DOMDocument $actual) {
            try {
                $expected = file_get_contents(__DIR__ . '/_files/email_templates_merged.xml');
                $expectedNorm = preg_replace('/xsi:noNamespaceSchemaLocation="[^"]*"/', '', $expected, 1);
                $actualNorm = preg_replace('/xsi:noNamespaceSchemaLocation="[^"]*"/', '', $actual->saveXML(), 1);
                \PHPUnit\Framework\Assert::assertXmlStringEqualsXmlString($expectedNorm, $actualNorm);
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
     */
    public function testReadUnknownModule()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Unable to determine a module');

        $this->_moduleDirResolver->expects($this->once())->method('getModuleName')->willReturn(null);
        $this->_converter->expects($this->never())->method('convert');
        $this->_model->read('scope');
    }
}
