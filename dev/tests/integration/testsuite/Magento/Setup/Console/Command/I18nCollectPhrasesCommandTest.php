<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;

class I18nCollectPhrasesCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var I18nCollectPhrasesCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $tester;

    protected function setUp(): void
    {
        $this->command = new I18nCollectPhrasesCommand();
        $this->tester = new CommandTester($this->command);
    }

    protected function tearDown(): void
    {
        $property = new \ReflectionProperty(\Magento\Setup\Module\I18n\ServiceLocator::class, '_dictionaryGenerator');
        $property->setAccessible(true);
        $property->setValue(null, null);
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
        $outputPath = BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/phrases.csv';
        $this->tester->execute(
            [
                'directory' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/phrases/',
                '--output' => $outputPath,
            ]
        );

        $handle = fopen($outputPath, 'r');
        $output = fread($handle, filesize($outputPath));
        $expected = file_get_contents(
            BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/expectedPhrases.csv'
        );
        $this->assertEquals($expected, $output);
        unlink($outputPath);
    }

    /**
     */
    public function testExecuteNonExistingPath()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Specified path doesn\'t exist');

        $this->tester->execute(
            [
                'directory' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/non_exist',
            ]
        );
    }

    /**
     */
    public function testExecuteMagentoFlagDirectoryPath()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Directory path is not needed when --magento flag is set.');

        $this->tester->execute(['directory' => 'a', '--magento' => true]);
    }

    /**
     */
    public function testExecuteNoMagentoFlagNoDirectoryPath()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Directory path is needed when --magento flag is not set.');

        $this->tester->execute([]);
    }
}
