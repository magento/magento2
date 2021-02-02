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
class ObjectManagerFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var callable[] */
    protected static $originalAutoloadFunctions;

    /** @var string */
    protected static $originalIncludePath;

    public static function setUpBeforeClass(): void
    {
        self::$originalAutoloadFunctions = spl_autoload_functions();
        self::$originalIncludePath = get_include_path();
    }

    /**
     * Avoid impact of initialized \Magento\Framework\Code\Generator\Autoloader on other tests
     */
    public static function tearDownAfterClass(): void
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
     */
    public function testCreateObjectManagerFactoryCouldBeOverridden()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Magento\\Framework\\App\\Test\\Unit\\ObjectManager\\FactoryStub::__construct');

        $rootPath = __DIR__ . '/_files/';
        $factory = Bootstrap::createObjectManagerFactory($rootPath, []);
        $factory->create([], false);
    }
}
