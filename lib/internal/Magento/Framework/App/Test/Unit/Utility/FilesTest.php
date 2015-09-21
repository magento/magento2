<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Utility;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;

class FilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Component\DirSearch|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dirSearch;

    /**
     * @var string
     */
    private $baseDir;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    protected function setUp()
    {
        $this->baseDir = __DIR__ . '/_files/foo';
        $this->componentRegistrar = new ComponentRegistrar();
        $this->dirSearch = $this->getMock('Magento\Framework\Component\DirSearch', [], [], '', false);
        Files::setInstance(new Files($this->componentRegistrar, $this->dirSearch, $this->baseDir));
    }

    protected function tearDown()
    {
        Files::setInstance();
    }

    public function testReadLists()
    {
        $result = Files::init()->readLists(__DIR__ . '/_files/*good.txt');

        // the braces
        $this->assertContains($this->baseDir . '/one.txt', $result);
        $this->assertContains($this->baseDir . '/two.txt', $result);

        // directory is returned as-is, without expanding contents recursively
        $this->assertContains($this->baseDir . '/bar', $result);

        // the * wildcard
        $this->assertContains($this->baseDir . '/baz/one.txt', $result);
        $this->assertContains($this->baseDir . '/baz/two.txt', $result);
    }

    public function testReadListsWrongPattern()
    {
        $this->assertSame([], Files::init()->readLists(__DIR__ . '/_files/no_good.txt'));
    }

    public function testReadListsCorruptedDir()
    {
        $result = Files::init()->readLists(__DIR__ . '/_files/list_corrupted_dir.txt');

        foreach ($result as $path) {
            $this->assertNotContains('bar/unknown', $path);
        }
    }

    public function testReadListsCorruptedFile()
    {
        $result = Files::init()->readLists(__DIR__ . '/_files/list_corrupted_file.txt');

        foreach ($result as $path) {
            $this->assertNotContains('unknown.txt', $path);
        }
    }

    public function testGetConfigFiles()
    {
        $this->dirSearch->expects($this->once())
            ->method('collectFiles')
            ->with(ComponentRegistrar::MODULE, '/etc/some.file')
            ->willReturn(['/one/some.file', '/two/some.file', 'some.other.file']);

        $expected = ['/one/some.file', '/two/some.file'];
        $actual = Files::init()->getConfigFiles('some.file', ['some.other.file'], false);
        $this->assertSame($expected, $actual);
        // Check that the result is cached (collectFiles() is called only once)
        $this->assertSame($expected, $actual);
    }

    public function testGetLayoutConfigFiles()
    {
        $this->dirSearch->expects($this->once())
            ->method('collectFiles')
            ->with(ComponentRegistrar::THEME, '/etc/some.file')
            ->willReturn(['/one/some.file', '/two/some.file']);

        $expected = ['/one/some.file', '/two/some.file'];
        $actual = Files::init()->getLayoutConfigFiles('some.file', false);
        $this->assertSame($expected, $actual);
        // Check that the result is cached (collectFiles() is called only once)
        $this->assertSame($expected, $actual);
    }
}
