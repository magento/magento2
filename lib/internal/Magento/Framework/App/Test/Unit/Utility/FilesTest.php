<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Utility;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;

/**
 * Test for Utility/Files class.
 *
 * @package Magento\Framework\App\Test\Unit\Utility
 */
class FilesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Component\DirSearch|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dirSearchMock;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dirSearchMock = $this->createMock(\Magento\Framework\Component\DirSearch::class);
        $fileUtilities = $objectManager->getObject(
            Files::class,
            [
                'dirSearch' => $this->dirSearchMock
            ]
        );
        Files::setInstance($fileUtilities);
    }

    protected function tearDown(): void
    {
        Files::setInstance();
    }

    public function testGetConfigFiles()
    {
        $this->dirSearchMock->expects($this->once())
            ->method('collectFiles')
            ->with(ComponentRegistrar::MODULE, '/etc/some.file')
            ->willReturn(['/one/some.file', '/two/some.file', 'some.other.file']);

        $expected = ['/one/some.file', '/two/some.file'];
        $actual = Files::init()->getConfigFiles('some.file', ['some.other.file'], false);
        $this->assertSame($expected, $actual);
        // Check that the result is cached (collectFiles() is called only once)
        $this->assertSame($expected, $actual);
    }

    public function testGetDbSchemaFiles()
    {
        $this->dirSearchMock->expects($this->once())
            ->method('collectFiles')
            ->with(ComponentRegistrar::MODULE, '/etc/db_schema.xml')
            ->willReturn(['First/Module/etc/db_schema.xml', 'Second/Module/etc/db_schema.xml']);

        $expected = [
            'First/Module/etc/db_schema.xml' => ['First/Module/etc/db_schema.xml'],
            'Second/Module/etc/db_schema.xml' => ['Second/Module/etc/db_schema.xml'],
        ];
        $actual = Files::init()->getDbSchemaFiles('db_schema.xml', ['Second/Module/etc/db_schema.xml']);
        $this->assertSame($expected, $actual);
    }

    public function testGetLayoutConfigFiles()
    {
        $this->dirSearchMock->expects($this->once())
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
