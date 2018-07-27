<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\Mapper\Helper;

class RelativePathConverterTest extends \PHPUnit_Framework_TestCase
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

        $this->setExpectedException('InvalidArgumentException', $exceptionMessage);
        $this->_sut->convert($nodePath, $relativePath);
    }

    /**
     * @dataProvider testConvertWithInvalidArgumentsDataProvider
     * @param string $nodePath
     * @param string $relativePath
     */
    public function testConvertWithInvalidArguments($nodePath, $relativePath)
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid arguments');
        $this->_sut->convert($nodePath, $relativePath);
    }

    /**
     * @dataProvider testConvertDataProvider
     * @param string $nodePath
     * @param string $relativePath
     * @param string $result
     */
    public function testConvert($nodePath, $relativePath, $result)
    {
        $this->assertEquals($result, $this->_sut->convert($nodePath, $relativePath));
    }

    /**
     * @return array
     */
    public function testConvertWithInvalidArgumentsDataProvider()
    {
        return [['', ''], ['some/node', ''], ['', 'some/node']];
    }

    /**
     * @return array
     */
    public function testConvertDataProvider()
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
