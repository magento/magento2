<?php
/**
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
namespace Magento\TestFramework\Inspection;

class WordsFinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $configFile
     * @param string $baseDir
     * @expectedException \Magento\TestFramework\Inspection\Exception
     * @dataProvider constructorExceptionDataProvider
     */
    public function testConstructorException($configFile, $baseDir)
    {
        new \Magento\TestFramework\Inspection\WordsFinder($configFile, $baseDir);
    }

    public function constructorExceptionDataProvider()
    {
        $fixturePath = __DIR__ . '/_files/';
        return array(
            'non-existing config file' => array($fixturePath . 'non-existing.xml', $fixturePath),
            'non-existing base dir' => array($fixturePath . 'config.xml', $fixturePath . 'non-existing-dir'),
            'broken config' => array($fixturePath . 'broken_config.xml', $fixturePath),
            'empty words config' => array($fixturePath . 'empty_words_config.xml', $fixturePath),
            'empty whitelisted path' => array($fixturePath . 'empty_whitelisted_path.xml', $fixturePath)
        );
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
            __DIR__ . '/_files/words_finder'
        );
        $actual = $wordsFinder->findWords($file);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function findWordsDataProvider()
    {
        $mainConfig = __DIR__ . '/_files/config.xml';
        $additionalConfig = __DIR__ . '/_files/config_additional.xml';
        $basePath = __DIR__ . '/_files/words_finder/';
        return array(
            'usual file' => array($mainConfig, $basePath . 'buffy.php', array('demon', 'vampire')),
            'whitelisted file' => array($mainConfig, $basePath . 'twilight/eclipse.php', array()),
            'partially whitelisted file' => array($mainConfig, $basePath . 'twilight/newmoon.php', array('demon')),
            'filename with bad word' => array(
                $mainConfig,
                $basePath . 'interview_with_the_vampire.php',
                array('vampire')
            ),
            'binary file, having name with bad word' => array(
                $mainConfig,
                $basePath . 'interview_with_the_vampire.zip',
                array('vampire')
            ),
            'words in multiple configs' => array(
                array($mainConfig, $additionalConfig),
                $basePath . 'buffy.php',
                array('demon', 'vampire', 'darkness')
            ),
            'whitelisted paths in multiple configs' => array(
                array($mainConfig, $additionalConfig),
                $basePath . 'twilight/newmoon.php',
                array('demon')
            ),
            'config must be whitelisted automatically' => array(
                $basePath . 'self_tested_config.xml',
                $basePath . 'self_tested_config.xml',
                array()
            )
        );
    }
}
