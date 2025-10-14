<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Framework\Xml\Parser;
use Magento\Setup\Fixtures\FixtureConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FixtureConfigTest extends TestCase
{
    /**
     * @var FixtureConfig
     */
    private $model;

    /**
     * @var MockObject
     */
    private $fileParserMock;

    protected function setUp(): void
    {
        $this->fileParserMock = $this->createPartialMock(Parser::class, ['getDom', 'xmlToArray']);

        $this->model = new FixtureConfig($this->fileParserMock);
    }

    public function testLoadConfigException()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage(
            'Profile configuration file `exception.file` is not readable or does not exists.'
        );
        $this->model->loadConfig('exception.file');
    }

    public function testLoadConfig()
    {
        $this->fileParserMock->expects($this->any())->method('xmlToArray')->willReturn(
            ['config' => [ 'profile' => ['some_key' => 'some_value']]]
        );

        $domMock = $this->createPartialMock(\DOMDocument::class, ['load', 'xinclude']);
        $domMock->expects($this->once())->method('load')->with('config.file')->willReturn(
            false
        );
        $domMock->expects($this->once())->method('xinclude');
        $this->fileParserMock->expects($this->exactly(2))->method('getDom')->willReturn($domMock);

        $this->model->loadConfig('config.file');
        $this->assertSame('some_value', $this->model->getValue('some_key'));
    }

    public function testGetValue()
    {
        $this->assertNull($this->model->getValue('null_key'));
    }
}

namespace Magento\Setup\Fixtures;

/**
 * Overriding the built-in PHP function since it cannot be mocked->
 *
 * The method is used in FixtureModel. loadConfig in an if statement. By overriding this method we are able to test
 * both of the possible cases based on the return value of is_readable.
 *
 * @param string $filename
 * @return bool
 */
function is_readable($filename)
{
    if (strpos($filename, 'exception') !== false) {
        return false;
    }
    return true;
}
