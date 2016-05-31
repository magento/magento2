<?php
/**
 * Test class for \Magento\Framework\Profiler\Driver\Standard\Output\Csvfile
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Test\Unit\Driver\Standard\Output;

class CsvfileTest extends \PHPUnit_Framework_TestCase
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
        $output = new \Magento\Framework\Profiler\Driver\Standard\Output\Csvfile($config);
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
