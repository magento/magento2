<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;

class I18nPackCommandTest extends \PHPUnit_Framework_TestCase
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
        $this->command = new I18nPackCommand();
        $this->tester = new CommandTester($this->command);
    }

    public function testExecute()
    {
        $this->tester->execute(
            [
                'source' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/i18n.csv',
                'pack' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/pack',
                'locale' => 'de_DE',
                '--allow-duplicates' => true,
            ]
        );

        $this->assertEquals('Successfully saved de_DE language package.' . PHP_EOL, $this->tester->getDisplay());
        $basePath = BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/pack/app/code';
        $this->assertFileExists($basePath . '/Magento/A/i18n/de_DE.csv');
        $this->assertFileExists($basePath . '/Magento/B/i18n/de_DE.csv');
        $this->assertFileExists($basePath . '/Magento/C/i18n/de_DE.csv');
        $this->assertFileExists($basePath . '/Magento/D/i18n/de_DE.csv');
        unlink($basePath . '/Magento/A/i18n/de_DE.csv');
        unlink($basePath . '/Magento/B/i18n/de_DE.csv');
        unlink($basePath . '/Magento/C/i18n/de_DE.csv');
        unlink($basePath . '/Magento/D/i18n/de_DE.csv');
        $this->recursiveRmdir(BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/pack');

    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot open dictionary file:
     */
    public function testExecuteNonExistingPath()
    {
        $nonExistPath = BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/non_exist.csv';
        $this->tester->execute(
            [
                'source' => $nonExistPath,
                'pack' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/pack',
                'locale' => 'de_DE',
                '--allow-duplicates' => true,
            ]
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Possible values for 'mode' option are 'replace' and 'merge'
     */
    public function testExecuteInvalidMode()
    {
        $this->tester->execute(
            [
                'source' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/i18n.csv',
                'pack' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/pack',
                'locale' => 'de_DE',
                '--allow-duplicates' => true,
                '--mode' => 'invalid'
            ]
        );
    }

    /**
     * Removes directories recursively
     *
     * @param string $dir
     * @return void
     */
    private function recursiveRmdir($dir)
    {
        if (is_dir($dir)) {
            $subdirs = scandir($dir);
            foreach ($subdirs as $subdir) {
                if ($subdir !== '.' && $subdir !== '..' && filetype($dir . '/' . $subdir) === 'dir') {
                    $this->recursiveRmdir($dir . '/' . $subdir);
                }
            }
            rmdir($dir);
        }
    }
}
