<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model\Country\Postcode\Config;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\Country\Postcode\Config\Reader
     */
    private $reader;

    protected function setUp()
    {
        $this->reader = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Directory\Model\Country\Postcode\Config\Reader::class
        );
    }

    public function testRead()
    {
        $result = $this->reader->read();

        $this->assertArrayHasKey('NL', $result);
        $this->assertArrayHasKey('pattern_1', $result['NL']);
        $this->assertArrayHasKey('pattern_2', $result['NL']);

        $this->assertEquals('test1', $result['NL']['pattern_1']['example']);
        $this->assertEquals('^[0-9]{4}\s[a-zA-Z]{2}$', $result['NL']['pattern_1']['pattern']);

        $this->assertEquals('test2', $result['NL']['pattern_2']['example']);
        $this->assertEquals('^[0-5]{4}[a-z]{2}$', $result['NL']['pattern_2']['pattern']);

        $this->assertArrayHasKey('NL_NEW', $result);
        $this->assertArrayHasKey('pattern_1', $result['NL_NEW']);

        $this->assertEquals('test1', $result['NL_NEW']['pattern_1']['example']);
        $this->assertEquals('^[0-2]{4}[A-Z]{2}$', $result['NL_NEW']['pattern_1']['pattern']);
    }
}
