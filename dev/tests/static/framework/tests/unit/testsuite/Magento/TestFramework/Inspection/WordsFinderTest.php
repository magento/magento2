<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Inspection;

use Magento\Framework\Component\ComponentRegistrar;

class WordsFinderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $configFile
     * @param string $baseDir
     * @dataProvider constructorExceptionDataProvider
     */
    public function testConstructorException($configFile, $baseDir)
    {
        $this->expectException(\Magento\TestFramework\Inspection\Exception::class);

        new \Magento\TestFramework\Inspection\WordsFinder($configFile, $baseDir, new ComponentRegistrar());
    }

    public static function constructorExceptionDataProvider()
    {
        $fixturePath = __DIR__ . '/_files/';
        return [
            'non-existing config file' => [$fixturePath . 'non-existing.xml', $fixturePath],
            'non-existing base dir' => [$fixturePath . 'config.xml', $fixturePath . 'non-existing-dir'],
            'broken config' => [$fixturePath . 'broken_config.xml', $fixturePath],
            'empty words config' => [$fixturePath . 'empty_words_config.xml', $fixturePath],
            'empty whitelisted path' => [$fixturePath . 'empty_whitelisted_path.xml', $fixturePath]
        ];
    }

    /**
     * @param string|array $configFiles
     * @param string $file
     * @param array $expected
     * @dataProvider findWordsDataProvider
     */
    public function testFindWords($configFiles, $file, $expected)
    {
        $wordsFinder = new \Magento\TestFramework\Inspection\WordsFinder(
            $configFiles,
            __DIR__ . '/_files/words_finder',
            new ComponentRegistrar()
        );
        $actual = $wordsFinder->findWords($file);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public static function findWordsDataProvider()
    {
        $mainConfig = __DIR__ . '/_files/config.xml';
        $additionalConfig = __DIR__ . '/_files/config_additional.xml';
        $basePath = __DIR__ . '/_files/words_finder/';
        return [
            'usual file' => [$mainConfig, $basePath . 'buffy.php', ['demon', 'vampire']],
            'whitelisted file' => [$mainConfig, $basePath . 'twilight/eclipse.php', []],
            'partially whitelisted file' => [$mainConfig, $basePath . 'twilight/newmoon.php', ['demon']],
            'filename with bad word' => [
                $mainConfig,
                $basePath . 'interview_with_the_vampire.php',
                ['vampire'],
            ],
            'binary file, having name with bad word' => [
                $mainConfig,
                $basePath . 'interview_with_the_vampire.zip',
                ['vampire'],
            ],
            'words in multiple configs' => [
                [$mainConfig, $additionalConfig],
                $basePath . 'buffy.php',
                ['demon', 'vampire', 'darkness'],
            ],
            'whitelisted paths in multiple configs' => [
                [$mainConfig, $additionalConfig],
                $basePath . 'twilight/newmoon.php',
                ['demon'],
            ],
            'config must be whitelisted automatically' => [
                $basePath . 'self_tested_config.xml',
                $basePath . 'self_tested_config.xml',
                [],
            ]
        ];
    }
}
