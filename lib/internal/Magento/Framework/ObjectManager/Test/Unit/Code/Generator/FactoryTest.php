<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Test\Unit\Code\Generator;

use Magento\Framework\Code\Generator\Io;
use Magento\Framework\ObjectManager\Code\Generator\Factory;
use Magento\Framework\ObjectManager\Code\Generator\Sample;
use \PHPUnit\Framework\MockObject\MockObject as MockObject;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Io|MockObject;
     */
    private $ioGenerator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->ioGenerator = $this->getMockBuilder(Io::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Checks a test case when factory generator creates auto-generated factories.
     */
    public function testGenerate()
    {
        require_once __DIR__ . '/_files/Sample.php';

        /** @var Factory|MockObject $generator */
        $generator = $this->getMockBuilder(Factory::class)
            ->setMethods(['_validateData'])
            ->setConstructorArgs(
                [
                    Sample::class,
                    null,
                    $this->ioGenerator
                ]
            )
            ->getMock();

        $this->ioGenerator
            ->method('generateResultFileName')
            ->with('\\' . \Magento\Framework\ObjectManager\Code\Generator\SampleFactory::class)
            ->willReturn('sample_file.php');
        $factoryCode = file_get_contents(__DIR__ . '/_files/SampleFactory.txt');
        $this->ioGenerator->method('writeResultFile')
            ->with('sample_file.php', $factoryCode);

        $generator->method('_validateData')
            ->willReturn(true);
        $generated = $generator->generate();
        $this->assertEquals('sample_file.php', $generated);
    }
}
