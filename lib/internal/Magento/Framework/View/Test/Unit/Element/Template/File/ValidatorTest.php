<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\Template\File;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\View\Element\Template\File\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * Resolver object
     *
     * @var Validator
     */
    private $validator;

    /**
     * Mock for view file system
     *
     * @var Filesystem|MockObject
     */
    private $fileSystemMock;

    /**
     * Mock for scope config
     *
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * Mock for root directory reader
     *
     * @var ReadInterface|MockObject
     */
    private $rootDirectoryMock;

    /**
     * Mock for compiled directory reader
     *
     * @var ReadInterface|MockObject
     */
    private $compiledDirectoryMock;

    /**
     * @var ComponentRegistrar|MockObject
     */
    private $componentRegistrar;

    /**
     * Test Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->fileSystemMock = $this->createMock(Filesystem::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->rootDirectoryMock = $this->getMockForAbstractClass(ReadInterface::class);
        $this->compiledDirectoryMock = $this->getMockForAbstractClass(ReadInterface::class);

        $this->fileSystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturnMap(
                [
                    [DirectoryList::ROOT, DriverPool::FILE, $this->rootDirectoryMock],
                    [DirectoryList::TMP_MATERIALIZATION_DIR, DriverPool::FILE, $this->compiledDirectoryMock],
                ]
            );

        $this->compiledDirectoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturn('/magento/var/compiled');

        $this->componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $this->componentRegistrar->expects($this->any())
            ->method('getPaths')
            ->willReturnMap(
                [
                    [ComponentRegistrar::MODULE, ['/magento/app/code/Some/Module']],
                    [ComponentRegistrar::THEME, ['/magento/themes/default']]
                ]
            );

        $fileDriverMock = $this->createMock(File::class);
        $fileDriverMock->expects($this->any())
            ->method('getRealPath')
            ->willReturnArgument(0);

        $this->validator = new Validator(
            $this->fileSystemMock,
            $this->scopeConfigMock,
            $this->componentRegistrar,
            null,
            $fileDriverMock
        );
    }

    /**
     * Test is file valid
     *
     * @param string $file
     * @param bool $expectedResult
     * @return void
     *
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($file, $expectedResult)
    {
        $this->rootDirectoryMock->expects($this->any())->method('isFile')->willReturn(true);
        $this->assertEquals($expectedResult, $this->validator->isValid($file));
    }

    /**
     * Data provider for testIsValid
     *
     * @return array
     */
    public static function isValidDataProvider()
    {
        return [
            'empty' => ['', false],
            '/magento/var/compiled/template.phtml' => ['/magento/var/compiled/template.phtml', true],
            '/magento/themes/default/template.phtml' => ['/magento/themes/default/template.phtml', true],
            '/magento/app/code/Some/Module/template.phtml' => ['/magento/app/code/Some/Module/template.phtml', true],
            '/magento/x' => ['/magento/x', false],
        ];
    }
}
