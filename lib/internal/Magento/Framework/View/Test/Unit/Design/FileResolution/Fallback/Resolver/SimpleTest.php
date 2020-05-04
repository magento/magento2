<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Design\FileResolution\Fallback\Resolver;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;
use Magento\Framework\View\Design\Fallback\RulePool;
use Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple;
use Magento\Framework\View\Design\ThemeInterface;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class SimpleTest extends TestCase
{
    /**
     * @var Read|MockObject
     */
    protected $directoryMock;

    /**
     * @var RuleInterface|MockObject
     */
    protected $ruleMock;

    /**
     * @var Simple
     */
    protected $object;

    /**
     * @var ReadFactory|MockObject
     */
    protected $readFactoryMock;

    /**
     * @var RulePool|MockObject
     */
    protected $rulePoolMock;

    /**
     * @var DirectoryList|MockObject
     */
    protected $directoryListMock;

    protected function setUp(): void
    {
        $this->directoryMock = $this->getMockBuilder(Read::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleMock = $this->getMockBuilder(RuleInterface::class)
            ->getMockForAbstractClass();
        $this->rulePoolMock = $this->getMockBuilder(RulePool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readFactoryMock = $this->getMockBuilder(ReadFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryListMock = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->rulePoolMock->expects($this->any())
            ->method('getRule')
            ->with('type')
            ->willReturn($this->ruleMock);

        $this->object = (new ObjectManager($this))->getObject(Simple::class, [
            'readFactory' => $this->readFactoryMock,
            'rulePool' => $this->rulePoolMock,
        ]);

        (new ObjectManager($this))->setBackwardCompatibleProperty(
            $this->object,
            'directoryList',
            $this->directoryListMock
        );
    }

    /**
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @param array $expectedParams
     *
     * @dataProvider resolveDataProvider
     */
    public function testResolve($area, $themePath, $locale, $module, array $expectedParams)
    {
        $expectedPath = '/some/dir/file.ext';
        $theme = $themePath ? $this->getMockForTheme($themePath) : null;
        if (!empty($expectedParams['theme'])) {
            $expectedParams['theme'] = $this->getMockForTheme($expectedParams['theme']);
        }

        $this->readFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->directoryMock);
        $this->ruleMock->expects($this->once())
            ->method('getPatternDirs')
            ->with($expectedParams)
            ->willReturn(['/some/dir']);
        $this->directoryMock->expects($this->once())
            ->method('isExist')
            ->with('file.ext')
            ->willReturn(true);
        $actualPath = $this->object->resolve('type', 'file.ext', $area, $theme, $locale, $module);
        $this->assertSame($expectedPath, $actualPath);
    }

    /**
     * @return array
     */
    public function resolveDataProvider()
    {
        return [
            'no area' => [
                null,
                'magento_theme',
                'en_US',
                'Magento_Module',
                [
                    'theme' => 'magento_theme',
                    'locale' => 'en_US',
                    'module_name' => 'Magento_Module',
                    'file' => 'file.ext',
                ],
            ],
            'no theme' => [
                'frontend',
                null,
                'en_US',
                'Magento_Module',
                [
                    'area' => 'frontend',
                    'locale' => 'en_US',
                    'module_name' => 'Magento_Module',
                    'file' => 'file.ext',
                ],
            ],
            'no locale' => [
                'frontend',
                'magento_theme',
                null,
                'Magento_Module',
                [
                    'area' => 'frontend',
                    'theme' => 'magento_theme',
                    'module_name' => 'Magento_Module',
                    'file' => 'file.ext',
                ],
            ],
            'no module' => [
                'frontend',
                'magento_theme',
                'en_US',
                null,
                [
                    'area' => 'frontend',
                    'theme' => 'magento_theme',
                    'locale' => 'en_US',
                    'file' => 'file.ext',
                ],
            ],
            'all params' => [
                'frontend',
                'magento_theme',
                'en_US',
                'Magento_Module',
                [
                    'area' => 'frontend',
                    'theme' => 'magento_theme',
                    'locale' => 'en_US',
                    'module_name' => 'Magento_Module',
                    'file' => 'file.ext',
                ],
            ],
        ];
    }

    public function testResolveSecurityException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('File path \'var/test/../file.ext\' is forbidden for security reasons.');
        $this->ruleMock->expects($this->once())
            ->method('getPatternDirs')
            ->willReturn([
                'var/test'
            ]);
        $directoryWeb = clone $this->directoryMock;
        $fileRead = clone $this->directoryMock;

        $this->directoryMock->expects($this->once())
            ->method('isExist')
            ->willReturn(true);
        $this->directoryListMock->expects($this->once())
            ->method('getPath')
            ->willReturn('lib_web');
        $this->readFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnMap([
                ['var/test', DriverPool::FILE, $this->directoryMock],
                ['lib_web', DriverPool::FILE, $directoryWeb],
                [false, DriverPool::FILE, $fileRead]
            ]);

        $this->object->resolve('type', '../file.ext', '', null, '', '');
    }

    public function testResolveSecurity()
    {
        $this->ruleMock->expects($this->once())
            ->method('getPatternDirs')
            ->willReturn([
                'var/test'
            ]);
        $directoryWeb = clone $this->directoryMock;
        $fileRead = clone $this->directoryMock;

        $this->directoryMock->expects($this->once())
            ->method('isExist')
            ->willReturn(true);
        $this->directoryListMock->expects($this->once())
            ->method('getPath')
            ->willReturn('lib_web');
        $this->readFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnMap([
                ['var/test', DriverPool::FILE, $this->directoryMock],
                ['lib_web', DriverPool::FILE, $directoryWeb],
                [false, DriverPool::FILE, $fileRead]
            ]);
        $directoryWeb->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn('var/test/web');
        $fileRead->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn('var/test/web/css');

        $this->assertEquals(
            'var/test/../file.ext',
            $this->object->resolve('type', '../file.ext', '', null, '', '')
        );
    }

    public function testResolveNoPatterns()
    {
        $this->ruleMock->expects($this->once())
            ->method('getPatternDirs')
            ->willReturn([]);

        $this->assertFalse(
            $this->object->resolve(
                'type',
                'file.ext',
                'frontend',
                $this->getMockForTheme('magento_theme'),
                'en_US',
                'Magento_Module'
            )
        );
    }

    public function testResolveNonexistentFile()
    {
        $this->readFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->directoryMock);
        $this->ruleMock->expects($this->once())
            ->method('getPatternDirs')
            ->willReturn(['some/dir']);
        $this->directoryMock->expects($this->once())
            ->method('isExist')
            ->willReturn(false);
        $this->assertFalse(
            $this->object->resolve(
                'type',
                'file.ext',
                'frontend',
                $this->getMockForTheme('magento_theme'),
                'en_US',
                'Magento_Module'
            )
        );
    }

    /**
     * @param string $themePath
     * @return ThemeInterface|MockObject
     */
    private function getMockForTheme($themePath)
    {
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $theme->expects($this->any())
            ->method('getThemePath')
            ->willReturn($themePath);
        return $theme;
    }
}
