<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure\Mapper\Helper;

use Magento\Config\Model\Config\Structure\Mapper\Helper\RelativePathConverter;
use PHPUnit\Framework\TestCase;

class RelativePathConverterTest extends TestCase
{
    /**
     * @var RelativePathConverter
     */
    protected $_sut;

    protected function setUp(): void
    {
        $this->_sut = new RelativePathConverter();
    }

    public function testConvertWithInvalidRelativePath()
    {
        $nodePath = 'node/path';
        $relativePath = '*/*/*/relativePath';

        $exceptionMessage = sprintf('Invalid relative path %s in %s node', $relativePath, $nodePath);

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage($exceptionMessage);
        $this->_sut->convert($nodePath, $relativePath);
    }

    /**
     * @dataProvider convertWithInvalidArgumentsDataProvider
     * @param string $nodePath
     * @param string $relativePath
     */
    public function testConvertWithInvalidArguments($nodePath, $relativePath)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid arguments');
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

    /**
     * @return array
     */
    public static function convertWithInvalidArgumentsDataProvider()
    {
        return [['', ''], ['some/node', ''], ['', 'some/node']];
    }

    /**
     * @return array
     */
    public static function convertDataProvider()
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
