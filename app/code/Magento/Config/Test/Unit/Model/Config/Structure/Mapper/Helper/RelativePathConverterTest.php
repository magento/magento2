<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\Mapper\Helper;

class RelativePathConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Structure\Mapper\Helper\RelativePathConverter
     */
    protected $_sut;

    protected function setUp()
    {
        $this->_sut = new \Magento\Config\Model\Config\Structure\Mapper\Helper\RelativePathConverter();
    }

    public function testConvertWithInvalidRelativePath()
    {
        $nodePath = 'node/path';
        $relativePath = '*/*/*/relativePath';

        $exceptionMessage = sprintf('Invalid relative path %s in %s node', $relativePath, $nodePath);

        $this->expectException('InvalidArgumentException', $exceptionMessage);
        $this->_sut->convert($nodePath, $relativePath);
    }

    /**
     * @dataProvider convertWithInvalidArgumentsDataProvider
     * @param string $nodePath
     * @param string $relativePath
     */
    public function testConvertWithInvalidArguments($nodePath, $relativePath)
    {
        $this->expectException('InvalidArgumentException', 'Invalid arguments');
        $this->_sut->convert($nodePath, $relativePath);
    }

    /**
     * @dataProvider convertDataProvider
     * @param string $nodePath
     * @param string $relativePath
     * @param string $result
     */
    public function testConvert($nodePath, $relativePath, $result)
    {
        $this->assertEquals($result, $this->_sut->convert($nodePath, $relativePath));
    }

    public function convertWithInvalidArgumentsDataProvider()
    {
        return [['', ''], ['some/node', ''], ['', 'some/node']];
    }

    public function convertDataProvider()
    {
        return [
            ['currentNode', 'relativeNode', 'relativeNode'],
            ['current/node/path', 'relative/node/path', 'relative/node/path'],
            ['current/node', 'siblingRelativeNode', 'current/siblingRelativeNode'],
            ['current/node', '*/siblingNode', 'current/siblingNode'],
            ['very/deep/node/hierarchy', '*/*/sourceNode', 'very/deep/sourceNode']
        ];
    }
}
