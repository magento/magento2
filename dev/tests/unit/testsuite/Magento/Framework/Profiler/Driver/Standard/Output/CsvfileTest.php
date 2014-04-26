<?php
/**
 * Test class for \Magento\Framework\Profiler\Driver\Standard\Output\Csvfile
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Profiler\Driver\Standard\Output;

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
        return array(
            'Default config' => array(
                'config' => array(),
                'filePath' => '/var/log/profiler.csv',
                'delimiter' => ',',
                'enclosure' => '"'
            ),
            'Custom config' => array(
                'config' => array(
                    'baseDir' => '/var/www/project/',
                    'filePath' => '/log/example.csv',
                    'delimiter' => "\t",
                    'enclosure' => '"'
                ),
                'filePath' => '/var/www/project/log/example.csv',
                'delimiter' => "\t",
                'enclosure' => '"'
            )
        );
    }
}
