<?php

namespace OAuthTest\Unit\Commen\Core;

use OAuth\Common\AutoLoader;

class AutoLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\Common\AutoLoader::__construct
     * @covers OAuth\Common\AutoLoader::register
     */
    public function testRegister()
    {
        $autoloader = new AutoLoader('Test', '/');

        $this->assertTrue($autoloader->register());
    }

    /**
     * @covers OAuth\Common\AutoLoader::__construct
     * @covers OAuth\Common\AutoLoader::register
     * @covers OAuth\Common\AutoLoader::unregister
     */
    public function testUnregister()
    {
        $autoloader = new AutoLoader('Test', '/');

        $this->assertTrue($autoloader->register());
        $this->assertTrue($autoloader->unregister());
    }

    /**
     * @covers OAuth\Common\AutoLoader::__construct
     * @covers OAuth\Common\AutoLoader::register
     * @covers OAuth\Common\AutoLoader::load
     */
    public function testLoadSuccess()
    {
        $autoloader = new AutoLoader('FakeProject', dirname(__DIR__) . '/../Mocks/Common');

        $this->assertTrue($autoloader->register());

        $someClass = new \FakeProject\NS\SomeClass();

        $this->assertTrue($someClass->isLoaded());
    }

    /**
     * @covers OAuth\Common\AutoLoader::__construct
     * @covers OAuth\Common\AutoLoader::register
     * @covers OAuth\Common\AutoLoader::load
     */
    public function testLoadSuccessExtraSlashedNamespace()
    {
        $autoloader = new AutoLoader('\\\\FakeProject', dirname(__DIR__) . '/../Mocks/Common');

        $this->assertTrue($autoloader->register());

        $someClass = new \FakeProject\NS\SomeClass();

        $this->assertTrue($someClass->isLoaded());
    }

    /**
     * @covers OAuth\Common\AutoLoader::__construct
     * @covers OAuth\Common\AutoLoader::register
     * @covers OAuth\Common\AutoLoader::load
     */
    public function testLoadSuccessExtraForwardSlashedPath()
    {
        $autoloader = new AutoLoader('FakeProject', dirname(__DIR__) . '/../Mocks/Common//');

        $this->assertTrue($autoloader->register());

        $someClass = new \FakeProject\NS\SomeClass();

        $this->assertTrue($someClass->isLoaded());
    }

    /**
     * @covers OAuth\Common\AutoLoader::__construct
     * @covers OAuth\Common\AutoLoader::register
     * @covers OAuth\Common\AutoLoader::load
     */
    public function testLoadSuccessExtraBackwardSlashedPath()
    {
        $autoloader = new AutoLoader('FakeProject', dirname(__DIR__) . '/../Mocks/Common\\');

        $this->assertTrue($autoloader->register());

        $someClass = new \FakeProject\NS\SomeClass();

        $this->assertTrue($someClass->isLoaded());
    }

    /**
     * @covers OAuth\Common\AutoLoader::__construct
     * @covers OAuth\Common\AutoLoader::register
     * @covers OAuth\Common\AutoLoader::load
     */
    public function testLoadSuccessExtraMixedSlashedPath()
    {
        $autoloader = new AutoLoader('FakeProject', dirname(__DIR__) . '/../Mocks/Common\\\\/\\//');

        $this->assertTrue($autoloader->register());

        $someClass = new \FakeProject\NS\SomeClass();

        $this->assertTrue($someClass->isLoaded());
    }

    /**
     * @covers OAuth\Common\AutoLoader::__construct
     * @covers OAuth\Common\AutoLoader::register
     * @covers OAuth\Common\AutoLoader::load
     */
    public function testLoadUnknownClass()
    {
        $autoloader = new AutoLoader('FakeProject', dirname(__DIR__) . '/../Mocks/Common\\\\/\\//');

        $this->assertTrue($autoloader->register());

        $this->assertFalse($autoloader->load('IDontExistClass'));
    }
}
