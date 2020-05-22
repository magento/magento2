<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

class MultipleStreamOutputTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MultipleStreamOutput
     */
    private $multipleStreamOutput;

    protected function setUp(): void
    {
        $this->multipleStreamOutput = new MultipleStreamOutput(
            [
                fopen(__DIR__ . '/_files/a.txt', 'a+'),
                fopen(__DIR__ . '/_files/b.txt', 'a+')
            ]
        );
    }

    protected function tearDown(): void
    {
        file_put_contents(__DIR__ . '/_files/a.txt', '');
        file_put_contents(__DIR__ . '/_files/b.txt', '');
    }

    /**
     */
    public function testCreateException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The StreamOutput class needs a stream as its first argument');

        $this->multipleStreamOutput = new MultipleStreamOutput(['a', 'b']);
    }

    public function testWriteln()
    {
        $this->multipleStreamOutput->writeln('Hello world');
        $this->assertEquals('Hello world' . PHP_EOL, file_get_contents(__DIR__ . '/_files/a.txt'));
        $this->assertEquals('Hello world' . PHP_EOL, file_get_contents(__DIR__ . '/_files/b.txt'));
    }

    public function testWrite()
    {
        $this->multipleStreamOutput->write('Hello world');
        $this->assertEquals('Hello world', file_get_contents(__DIR__ . '/_files/a.txt'));
        $this->assertEquals('Hello world', file_get_contents(__DIR__ . '/_files/b.txt'));
    }
}
