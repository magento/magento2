<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Design\FileResolution\Fallback\Resolver;

use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Design\Fallback\RulePool;
use \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple;

use Magento\Framework\App\Filesystem\DirectoryList;

class SimpleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    /**
     * @var \Magento\Framework\View\Design\Fallback\Rule\RuleInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleMock;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple
     */
    protected $object;

    /**
     * @var ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readFactoryMock;

    /**
     * @var RulePool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rulePoolMock;

    /**
     * @var DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryListMock;

    protected function setUp()
    {
        $this->directoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\Read::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleMock = $this->getMockBuilder(\Magento\Framework\View\Design\Fallback\Rule\RuleInterface::class)
            ->getMockForAbstractClass();
        $this->rulePoolMock = $this->getMockBuilder(\Magento\Framework\View\Design\Fallback\RulePool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readFactoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadFactory::class)
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File path 'var/test/../file.ext' is forbidden for security reasons.
     */
    public function testResolveSecurityException()
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
     * @return \Magento\Framework\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockForTheme($themePath)
    {
        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $theme->expects($this->any())
            ->method('getThemePath')
            ->willReturn($themePath);
        return $theme;
    }
}
