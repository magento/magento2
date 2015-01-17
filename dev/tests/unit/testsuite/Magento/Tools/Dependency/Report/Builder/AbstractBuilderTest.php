<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Dependency\Report\Builder;

class AbstractBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Dependency\ParserInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dependenciesParserMock;

    /**
     * @var \Magento\Tools\Dependency\Report\WriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reportWriterMock;

    /**
     * @var \Magento\Tools\Dependency\Report\Builder\AbstractBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $builder;

    protected function setUp()
    {
        $this->dependenciesParserMock = $this->getMock('Magento\Tools\Dependency\ParserInterface');
        $this->reportWriterMock = $this->getMock('Magento\Tools\Dependency\Report\WriterInterface');

        $this->builder = $this->getMockForAbstractClass(
            'Magento\Tools\Dependency\Report\Builder\AbstractBuilder',
            ['dependenciesParser' => $this->dependenciesParserMock, 'reportWriter' => $this->reportWriterMock]
        );
    }

    /**
     * @param array $options
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Passed option section "parse" is wrong.
     * @dataProvider dataProviderWrongParseOptions
     */
    public function testBuildWithWrongParseOptions($options)
    {
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Passed option section "write" is wrong.
     * @dataProvider dataProviderWrongWriteOptions
     */
    public function testBuildWithWrongWriteOptions($options)
    {
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
        $configMock = $this->getMock('\Magento\Tools\Dependency\Report\Data\ConfigInterface');

        $this->dependenciesParserMock->expects(
            $this->once()
        )->method(
            'parse'
        )->with(
            $options['parse']
        )->will(
            $this->returnValue($parseResult)
        );
        $this->builder->expects(
            $this->once()
        )->method(
            'buildData'
        )->with(
            $parseResult
        )->will(
            $this->returnValue($configMock)
        );
        $this->reportWriterMock->expects($this->once())->method('write')->with($options['write'], $configMock);

        $this->builder->build($options);
    }
}
