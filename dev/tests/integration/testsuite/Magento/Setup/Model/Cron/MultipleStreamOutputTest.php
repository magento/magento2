<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

class MultipleStreamOutputTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MultipleStreamOutput
     */
    private $multipleStreamOutput;

    public function setUp()
    {
        $this->multipleStreamOutput = new MultipleStreamOutput(
            [
                fopen(__DIR__ . '/_files/a.txt', 'a+'),
                fopen(__DIR__ . '/_files/b.txt', 'a+')
            ]
        );
    }

    public function tearDown()
    {
        file_put_contents(__DIR__ . '/_files/a.txt', '');
        file_put_contents(__DIR__ . '/_files/b.txt', '');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The StreamOutput class needs a stream as its first argument
     */
    public function testCreateException()
    {
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
