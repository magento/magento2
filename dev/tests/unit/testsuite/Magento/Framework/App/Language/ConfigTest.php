<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Language;

/**
 * Test for configuration of language
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testConfiguration()
    {
        $languageXml = file_get_contents(__DIR__ . '/_files/language.xml');
        $languageConfig = new Config($languageXml);
        $this->assertEquals('en_GB', $languageConfig->getCode());
        $this->assertEquals('magento', $languageConfig->getVendor());
        $this->assertEquals('en_gb', $languageConfig->getPackage());
        $this->assertEquals('100', $languageConfig->getSortOrder());
        $this->assertEquals(
            [
                ['vendor' => 'oxford-university', 'package' => 'en_us'],
                ['vendor' => 'oxford-university', 'package' => 'en_gb'],
            ],
            $languageConfig->getUses()
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage Opening and ending tag mismatch: xlanguage line 7 and language
     */
    public function testFailedConfiguration()
    {
	$languageXml = file_get_contents(__DIR__ . '/_files/language_invalid.xml');
	$languageConfig = new Config($languageXml);
    }
}
