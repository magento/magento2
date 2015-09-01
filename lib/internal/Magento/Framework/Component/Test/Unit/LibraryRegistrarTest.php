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
        LibraryRegistrar::register("test_library_one", "some/path/name/one");
        LibraryRegistrar::register("test_library_two", "some/path/name/two");
    }

    public function testGetPaths()
    {
        $expected = [
            'test_library_one' => "some/path/name/one",
            'test_library_two' => "some/path/name/two",
        ];
        $this->assertSame($expected, $this->library->getPaths());
    }

    public function testGetPath()
    {
        $this->assertSame("some/path/name/one", $this->library->getPath('test_library_one'));
        $this->assertSame("some/path/name/two", $this->library->getPath('test_library_two'));
    }
}
