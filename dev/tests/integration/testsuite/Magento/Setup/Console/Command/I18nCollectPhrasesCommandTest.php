<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;

class I18nCollectPhrasesCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var I18nCollectPhrasesCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $tester;

    public function setUp()
    {
        $this->command = new I18nCollectPhrasesCommand();
        $this->tester = new CommandTester($this->command);
    }

    public function tearDown()
    {
        $property = new \ReflectionProperty('\Magento\Setup\Module\I18n\ServiceLocator', '_dictionaryGenerator');
        $property->setAccessible(true);
        $property->setValue(null);
        $property->setAccessible(false);
    }

    public function testExecuteConsoleOutput()
    {
        $this->tester->execute(
            [
                'directory' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/',
            ]
        );

        $this->assertEquals('Dictionary successfully processed.' . PHP_EOL, $this->tester->getDisplay());
    }

    public function testExecuteCsvOutput()
    {
        $outputPath = BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/output.csv';
        $this->tester->execute(
            [
                'directory' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/',
                '--output' => $outputPath,
            ]
        );

        $handle = fopen($outputPath, 'r');
        $output = fread($handle, filesize($outputPath));
        $expected = '"Hello world","Hello world"' . PHP_EOL . '"Foo bar","Foo bar"' . PHP_EOL;
        $this->assertEquals($expected, $output);
        unlink($outputPath);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Specified path doesn't exist
     */
    public function testExecuteNonExistingPath()
    {
        $this->tester->execute(
            [
                'directory' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/non_exist',
            ]
        );
    }
}
