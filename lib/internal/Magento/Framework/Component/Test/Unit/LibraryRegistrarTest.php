<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Component\Test\Unit;

use Magento\Framework\Component\LibraryRegistrar;

class LibraryRegistrarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * library registrar object
     */
    private $library;

    protected function setUp()
    {
        $this->library = new LibraryRegistrar();
    }

    public function testGetPaths()
    {
        LibraryRegistrar::register("test_library_one", "some/path/name/one");
        LibraryRegistrar::register("test_library_two", "some/path/name/two");
        $expected = [
            'test_library_one' => "some/path/name/one",
            'test_library_two' => "some/path/name/two",
        ];
        $this->assertSame($expected, $this->library->getPaths());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage test_library_one already exists
     */
    public function testRegistrarWithException()
    {
        LibraryRegistrar::register("test_library_one", "some/path/name/one");
    }

    public function testGetPath()
    {
        $this->assertSame("some/path/name/one", $this->library->getPath('test_library_one'));
        $this->assertSame("some/path/name/two", $this->library->getPath('test_library_two'));
    }
}
