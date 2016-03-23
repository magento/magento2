<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    protected function setUp()
    {
        $this->componentRegistrar = new ComponentRegistrar();
        $this->dirSearch = $this->getMock('Magento\Framework\Component\DirSearch', [], [], '', false);
        $themePackageList = $this->getMock('Magento\Framework\View\Design\Theme\ThemePackageList', [], [], '', false);
        Files::setInstance(new Files($this->componentRegistrar, $this->dirSearch, $themePackageList));
    }

    protected function tearDown()
    {
        Files::setInstance();
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
