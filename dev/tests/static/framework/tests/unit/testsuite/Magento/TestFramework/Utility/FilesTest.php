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
namespace Magento\TestFramework\Utility;

class FilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private static $baseDir;

    public static function setUpBeforeClass()
    {
        self::$baseDir = __DIR__ . '/_files/foo';
        Files::setInstance(new Files(self::$baseDir));
    }

    public static function tearDownAfterClass()
    {
        Files::setInstance();
    }

    public function testReadLists()
    {
        $result = Files::init()->readLists(__DIR__ . '/_files/*good.txt');

        // the braces
        $this->assertContains(self::$baseDir . '/one.txt', $result);
        $this->assertContains(self::$baseDir . '/two.txt', $result);

        // directory is returned as-is, without expanding contents recursively
        $this->assertContains(self::$baseDir . '/bar', $result);

        // the * wildcard
        $this->assertContains(self::$baseDir . '/baz/one.txt', $result);
        $this->assertContains(self::$baseDir . '/baz/two.txt', $result);
    }

    public function testReadListsWrongPattern()
    {
        $this->assertSame(array(), Files::init()->readLists(__DIR__ . '/_files/no_good.txt'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The glob() pattern 'bar/unknown' didn't return any result.
     */
    public function testReadListsCorruptedDir()
    {
        Files::init()->readLists(__DIR__ . '/_files/list_corrupted_dir.txt');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The glob() pattern 'unknown.txt' didn't return any result.
     */
    public function testReadListsCorruptedFile()
    {
        Files::init()->readLists(__DIR__ . '/_files/list_corrupted_file.txt');
    }
}
