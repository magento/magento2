<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Bootstrap;

/**
 * @covers \Magento\Framework\App\ObjectManagerFactory
 */
class ObjectManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var callable[] */
    protected static $originalAutoloadFunctions;

    /** @var string */
    protected static $originalIncludePath;

    public static function setUpBeforeClass()
    {
        self::$originalAutoloadFunctions = spl_autoload_functions();
        self::$originalIncludePath = get_include_path();
    }

    /**
     * Avoid impact of initialized \Magento\Framework\Code\Generator\Autoloader on other tests
     */
    public static function tearDownAfterClass()
    {
        foreach (spl_autoload_functions() as $autoloadFunction) {
            spl_autoload_unregister($autoloadFunction);
        }
        foreach (self::$originalAutoloadFunctions as $autoloadFunction) {
            spl_autoload_register($autoloadFunction);
        }
        set_include_path(self::$originalIncludePath);
        \Magento\Framework\Filesystem\Io\File::rmdirRecursive(__DIR__ . '/_files/var/');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Magento\Framework\App\Test\Unit\ObjectManager\FactoryStub::__construct
     */
    public function testCreateObjectManagerFactoryCouldBeOverridden()
    {
        $rootPath = __DIR__ . '/_files/';
        $factory = Bootstrap::createObjectManagerFactory($rootPath, []);
        $factory->create([], false);
    }
}
