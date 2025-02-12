<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model\Template\Config;

use Magento\Catalog\Model\Attribute\Config\Converter as AttributeConverter;
use Magento\Email\Model\Template\Config\Converter;
use Magento\Email\Model\Template\Config\FileIterator;
use Magento\Email\Model\Template\Config\FileResolver;
use Magento\Email\Model\Template\Config\Reader;
use Magento\Email\Model\Template\Config\SchemaLocator;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\Filesystem\File\Read;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Module\Dir\ReverseResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReaderTest extends TestCase
{
    /**
     * @var Reader
     */
    protected $_model;

    /**
     * @var AttributeConverter|MockObject
     */
    protected $_converter;

    /**
     * @var ReverseResolver|MockObject
     */
    protected $_moduleDirResolver;

    /**
     * @var Read|MockObject
     */
    protected $read;

    /**
     * Fixtures paths.
     *
     * @var array
     */
    protected $_paths;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $fileResolver = $this->createMock(FileResolver::class);
        $this->_paths = [
            __DIR__ . '/_files/Fixture/ModuleOne/etc/email_templates_one.xml',
            __DIR__ . '/_files/Fixture/ModuleTwo/etc/email_templates_two.xml'
        ];

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
            'Magento_Email'
        )->willReturn(
            'stub'
        );
        $schemaLocator = new SchemaLocator($moduleReader);

        $validationStateMock = $this->getMockForAbstractClass(ValidationStateInterface::class);
        $validationStateMock->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(false);

        $this->_moduleDirResolver = $this->createMock(ReverseResolver::class);
        $readFactory = $this->createMock(ReadFactory::class);
        $this->read = $this->createMock(Read::class);
        $readFactory->expects($this->any())->method('create')->willReturn($this->read);

        $fileIterator = new FileIterator(
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

        $this->_model = new Reader(
            $fileResolver,
            $this->_converter,
            $schemaLocator,
            $validationStateMock
        );
    }

    /**
     * @return void
     */
    public function testRead(): void
    {
        $this->read
            ->method('readAll')
            ->willReturnOnConsecutiveCalls(file_get_contents($this->_paths[0]), file_get_contents($this->_paths[1]));
        $this->_moduleDirResolver
            ->method('getModuleName')
            ->willReturnCallback(
                function ($arg) {
                    if ($arg === __DIR__ . '/_files/Fixture/ModuleOne/etc/email_templates_one.xml') {
                        return 'Fixture_ModuleOne';
                    } elseif ($arg === __DIR__ . '/_files/Fixture/ModuleTwo/etc/email_templates_two.xml') {
                        return 'Fixture_ModuleTwo';
                    }
                }
            );
        $constraint = function (\DOMDocument $actual) {
            try {
                $expected = file_get_contents(__DIR__ . '/_files/email_templates_merged.xml');
                $expectedNorm = preg_replace('/xsi:noNamespaceSchemaLocation="[^"]*"/', '', $expected, 1);
                $actualNorm = preg_replace('/xsi:noNamespaceSchemaLocation="[^"]*"/', '', $actual->saveXML(), 1);
                Assert::assertXmlStringEqualsXmlString($expectedNorm, $actualNorm);
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
     * @return void
     */
    public function testReadUnknownModule(): void
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Unable to determine a module');
        $this->_moduleDirResolver->expects($this->once())->method('getModuleName')->willReturn(null);
        $this->_converter->expects($this->never())->method('convert');
        $this->_model->read('scope');
    }
}
