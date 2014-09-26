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
namespace Magento\Framework\App;

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
        \Magento\Framework\Io\File::rmdirRecursive(__DIR__ . '/_files/var/');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Magento\Framework\App\FactoryStub::__construct
     */
    public function testCreateObjectManagerFactoryCouldBeOverridden()
    {
        $rootPath = __DIR__ . '/_files/';
        $factory = new ObjectManagerFactory();
        $factory->create($rootPath, array(), false);
    }
}
