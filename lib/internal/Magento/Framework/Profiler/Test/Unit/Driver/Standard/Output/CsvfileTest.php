<?php declare(strict_types=1);
/**
 * Test class for \Magento\Framework\Profiler\Driver\Standard\Output\Csvfile
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Test\Unit\Driver\Standard\Output;

use Magento\Framework\Profiler\Driver\Standard\Output\Csvfile;
use PHPUnit\Framework\TestCase;

class CsvfileTest extends TestCase
{
    /**
     * @dataProvider constructorProvider
     * @param array $config
     * @param string $expectedFilePath
     * @param string $expectedDelimiter
     * @param string $expectedEnclosure
     */
    public function testConstructor($config, $expectedFilePath, $expectedDelimiter, $expectedEnclosure)
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $output = new Csvfile($config);
        $this->assertAttributeEquals($expectedFilePath, '_filePath', $output);
        $this->assertAttributeEquals($expectedDelimiter, '_delimiter', $output);
        $this->assertAttributeEquals($expectedEnclosure, '_enclosure', $output);
    }

    /**
     * @return array
     */
    public function constructorProvider()
    {
        return [
            'Default config' => [
                'config' => [],
                'filePath' => '/var/log/profiler.csv',
                'delimiter' => ',',
                'enclosure' => '"',
            ],
            'Custom config' => [
                'config' => [
                    'baseDir' => '/var/www/project/',
                    'filePath' => '/log/example.csv',
                    'delimiter' => "\t",
                    'enclosure' => '"',
                ],
                'filePath' => '/var/www/project/log/example.csv',
                'delimiter' => "\t",
                'enclosure' => '"',
            ]
        ];
    }
}
