<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Builder;

class AbstractBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Dependency\ParserInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dependenciesParserMock;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\WriterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $reportWriterMock;

    /**
     * @var \Magento\Setup\Module\Dependency\Report\Builder\AbstractBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $builder;

    protected function setUp(): void
    {
        $this->dependenciesParserMock = $this->createMock(\Magento\Setup\Module\Dependency\ParserInterface::class);
        $this->reportWriterMock = $this->createMock(\Magento\Setup\Module\Dependency\Report\WriterInterface::class);

        $this->builder = $this->getMockForAbstractClass(
            \Magento\Setup\Module\Dependency\Report\Builder\AbstractBuilder::class,
            ['dependenciesParser' => $this->dependenciesParserMock, 'reportWriter' => $this->reportWriterMock]
        );
    }

    /**
     * @param array $options
     * @dataProvider dataProviderWrongParseOptions
     */
    public function testBuildWithWrongParseOptions($options)
    {
        $this->expectException(\InvalidArgumentException::class);
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
        $this->expectException(\InvalidArgumentException::class);
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
        $configMock = $this->createMock(\Magento\Setup\Module\Dependency\Report\Data\ConfigInterface::class);

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
