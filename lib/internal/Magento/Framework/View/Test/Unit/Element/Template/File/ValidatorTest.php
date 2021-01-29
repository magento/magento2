<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Template\File;

use \Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use \Magento\Framework\Filesystem\DriverPool;

/**
 * Class ValidatorTest
 * @package Magento\Framework\View\Test\Unit\Element\Template\File
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Resolver object
     *
     * @var \Magento\Framework\View\Element\Template\File\Validator
     */
    private $validator;

    /**
     * Mock for view file system
     *
     * @var \Magento\Framework\FileSystem|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fileSystemMock;

    /**
     * Mock for scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * Mock for root directory reader
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $rootDirectoryMock;

    /**
     * Mock for compiled directory reader
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $compiledDirectoryMock;

    /**
     * @var ComponentRegistrar|\PHPUnit\Framework\MockObject\MockObject
     */
    private $componentRegistrar;

    /**
     * Test Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->fileSystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->rootDirectoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $this->compiledDirectoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);

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

        $this->componentRegistrar = $this->createMock(\Magento\Framework\Component\ComponentRegistrar::class);
        $this->componentRegistrar->expects($this->any())
            ->method('getPaths')
            ->willReturnMap(
                
                    [
                        [ComponentRegistrar::MODULE, ['/magento/app/code/Some/Module']],
                        [ComponentRegistrar::THEME, ['/magento/themes/default']]
                    ]
                
            );

        $fileDriverMock = $this->createMock(\Magento\Framework\Filesystem\Driver\File::class);
        $fileDriverMock->expects($this->any())
            ->method('getRealPath')
            ->willReturnArgument(0);

        $this->validator = new \Magento\Framework\View\Element\Template\File\Validator(
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
    public function isValidDataProvider()
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
