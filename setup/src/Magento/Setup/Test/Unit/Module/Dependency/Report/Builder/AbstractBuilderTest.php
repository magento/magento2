<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Builder;

use Magento\Setup\Module\Dependency\ParserInterface;
use Magento\Setup\Module\Dependency\Report\Builder\AbstractBuilder;
use Magento\Setup\Module\Dependency\Report\Data\ConfigInterface;
use Magento\Setup\Module\Dependency\Report\WriterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
        $this->dependenciesParserMock = $this->getMockForAbstractClass(ParserInterface::class);
        $this->reportWriterMock = $this->getMockForAbstractClass(WriterInterface::class);

        $this->builder = $this->getMockForAbstractClass(
            AbstractBuilder::class,
            ['dependenciesParser' => $this->dependenciesParserMock, 'reportWriter' => $this->reportWriterMock]
        );
    }

    /**
     * @param array $options
     * @dataProvider dataProviderWrongParseOptions
     */
    public function testBuildWithWrongParseOptions($options)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Passed option section "parse" is wrong.');
        $this->builder->build($options);
    }

    /**
     * @return array
     */
    public function dataProviderWrongParseOptions()
    {
        return [[['write' => [1, 2]]], [['parse' => [], 'write' => [1, 2]]]];
    }

    /**
     * @param array $options
     * @dataProvider dataProviderWrongWriteOptions
     */
    public function testBuildWithWrongWriteOptions($options)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Passed option section "write" is wrong.');
        $this->builder->build($options);
    }

    /**
     * @return array
     */
    public function dataProviderWrongWriteOptions()
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
        $configMock = $this->getMockForAbstractClass(ConfigInterface::class);

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
