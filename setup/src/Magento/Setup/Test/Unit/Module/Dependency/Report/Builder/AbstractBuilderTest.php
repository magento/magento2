<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Builder;

use Magento\Setup\Module\Dependency\ParserInterface;
use Magento\Setup\Module\Dependency\Report\Builder\AbstractBuilder;
use Magento\Setup\Module\Dependency\Report\Data\ConfigInterface;
use Magento\Setup\Module\Dependency\Report\WriterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AbstractBuilderTest extends TestCase
{
    /**
     * @var ParserInterface|MockObject
     */
    protected $dependenciesParserMock;

    /**
     * @var WriterInterface|MockObject
     */
    protected $reportWriterMock;

    /**
     * @var AbstractBuilder|MockObject
     */
    protected $builder;

    protected function setUp(): void
    {
        $this->dependenciesParserMock = $this->createMock(ParserInterface::class);
        $this->reportWriterMock = $this->createMock(WriterInterface::class);

        $this->builder = $this->getMockBuilder(AbstractBuilder::class)
            ->setConstructorArgs([$this->dependenciesParserMock, $this->reportWriterMock])
            ->onlyMethods(['buildData'])
            ->getMock();
    }

    /**
     * @param array $options
     */
    #[DataProvider('dataProviderWrongParseOptions')]
    public function testBuildWithWrongParseOptions($options)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Passed option section "parse" is wrong.');
        $this->builder->build($options);
    }

    /**
     * @return array
     */
    public static function dataProviderWrongParseOptions()
    {
        return [[['write' => [1, 2]]], [['parse' => [], 'write' => [1, 2]]]];
    }

    /**
     * @param array $options
     */
    #[DataProvider('dataProviderWrongWriteOptions')]
    public function testBuildWithWrongWriteOptions($options)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Passed option section "write" is wrong.');
        $this->builder->build($options);
    }

    /**
     * @return array
     */
    public static function dataProviderWrongWriteOptions()
    {
        return [[['parse' => [1, 2]]], [['parse' => [1, 2], 'write' => []]]];
    }

    public function testBuild()
    {
        $options = [
            'parse' => ['files_for_parse' => [1, 2, 3]],
            'write' => ['report_filename' => 'some_filename'],
        ];

        $parseResult = ['foo', 'bar', 'baz'];
        $configMock = $this->createMock(ConfigInterface::class);

        $this->dependenciesParserMock->expects(
            $this->once()
        )->method(
            'parse'
        )->with(
            $options['parse']
        )->willReturn(
            $parseResult
        );
        $this->builder->expects(
            $this->once()
        )->method(
            'buildData'
        )->with(
            $parseResult
        )->willReturn(
            $configMock
        );
        $this->reportWriterMock->expects($this->once())->method('write')->with($options['write'], $configMock);

        $this->builder->build($options);
    }
}
