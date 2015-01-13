<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Utility;

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
        $this->assertSame([], Files::init()->readLists(__DIR__ . '/_files/no_good.txt'));
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
