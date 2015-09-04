<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Component\Test\Unit;

use Magento\Framework\Component\LanguageRegistrar;

class LanguageRegistrarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Language registrar object
     */
    private $language;

    protected function setUp()
    {
        $this->language = LanguageRegistrar::getInstance();
    }

    public function testGetPaths()
    {
        $this->language->register("test_language_one", "some/path/name/one");
        $this->language->register("test_language_two", "some/path/name/two");
        $expected = [
            'test_language_one' => "some/path/name/one",
            'test_language_two' => "some/path/name/two",
        ];
        $this->assertSame($expected, $this->language->getPaths());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage test_language_one already exists
     */
    public function testRegistrarWithException()
    {
        $this->language->register("test_language_one", "some/path/name/one");
    }

    public function testGetPath()
    {
        $this->assertSame("some/path/name/one", $this->language->getPath('test_language_one'));
        $this->assertSame("some/path/name/two", $this->language->getPath('test_language_two'));
    }
}
