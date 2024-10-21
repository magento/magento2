<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model\Country\Postcode\Config;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var Reader
     */
    private $reader;

    protected function setUp(): void
    {
        $this->reader = Bootstrap::getObjectManager()
            ->create(Reader::class);
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

        $this->assertArrayHasKey('AR', $result);
        $this->assertArrayHasKey('pattern_1', $result['AR']);
        $this->assertArrayHasKey('pattern_2', $result['AR']);
        $this->assertEquals('1234', $result['AR']['pattern_1']['example']);
        $this->assertEquals('^[0-9]{4}$', $result['AR']['pattern_1']['pattern']);
        $this->assertEquals('A1234BCD', $result['AR']['pattern_2']['example']);
        $this->assertEquals('^[a-zA-z]{1}[0-9]{4}[a-zA-z]{3}$', $result['AR']['pattern_2']['pattern']);

        $this->assertArrayHasKey('KR', $result);
        $this->assertArrayHasKey('pattern_1', $result['KR']);
        $this->assertArrayHasKey('pattern_2', $result['KR']);
        $this->assertEquals('123-456', $result['KR']['pattern_1']['example']);
        $this->assertEquals('^[0-9]{3}-[0-9]{3}$', $result['KR']['pattern_1']['pattern']);
        $this->assertEquals('12345', $result['KR']['pattern_2']['example']);
        $this->assertEquals('^[0-9]{5}$', $result['KR']['pattern_2']['pattern']);
    }
}
