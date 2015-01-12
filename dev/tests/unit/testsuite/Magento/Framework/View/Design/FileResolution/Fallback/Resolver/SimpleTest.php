<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback\Resolver;

use Magento\Framework\App\Filesystem\DirectoryList;

class SimpleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directory;

    /**
     * @var \Magento\Framework\View\Design\Fallback\Rule\RuleInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rule;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple
     */
    private $object;

    protected function setUp()
    {
        $this->directory = $this->getMock('\Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $this->directory->expects($this->any())
            ->method('getRelativePath')
            ->will($this->returnArgument(0));
        $filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->will($this->returnValue($this->directory));
        $this->rule = $this->getMock(
            '\Magento\Framework\View\Design\Fallback\Rule\RuleInterface', [], [], '', false
        );
        $rulePool = $this->getMock('Magento\Framework\View\Design\Fallback\RulePool', [], [], '', false);
        $rulePool->expects($this->any())
            ->method('getRule')
            ->with('type')
            ->will($this->returnValue($this->rule));
        $this->cache = $this->getMockForAbstractClass(
            'Magento\Framework\View\Design\FileResolution\Fallback\CacheDataInterface'
        );
        $this->object = new Simple($filesystem, $rulePool, $this->cache);
    }

    /**
     * Cache is empty
     *
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

        $this->cache->expects($this->once())
            ->method('getFromCache')
            ->with('type', 'file.ext', $area, $themePath, $locale, $module)
            ->will($this->returnValue(false));
        $this->directory->expects($this->never())
            ->method('getAbsolutePath');
        $this->rule->expects($this->once())
            ->method('getPatternDirs')
            ->with($expectedParams)
            ->will($this->returnValue(['/some/dir']));
        $this->directory->expects($this->once())
            ->method('isExist')
            ->with($expectedPath)
            ->will($this->returnValue(true));
        $this->cache->expects($this->once())
            ->method('saveToCache')
            ->with($expectedPath, 'type', 'file.ext', $area, $themePath, $locale, $module);
        $actualPath = $this->object->resolve(
            'type', 'file.ext', $area, $theme, $locale, $module
        );
        $this->assertSame($expectedPath, $actualPath);
    }

    /**
     * @return array
     */
    public function resolveDataProvider()
    {
        return [
            'no area' => [
                null, 'magento_theme', 'en_US', 'Magento_Module',
                [
                    'theme' => 'magento_theme',
                    'locale' => 'en_US',
                    'namespace' => 'Magento',
                    'module' => 'Module',
                ],
            ],
            'no theme' => [
                'frontend', null, 'en_US', 'Magento_Module',
                [
                    'area' => 'frontend',
                    'locale' => 'en_US',
                    'namespace' => 'Magento',
                    'module' => 'Module',
                ],
            ],
            'no locale' => [
                'frontend', 'magento_theme', null, 'Magento_Module',
                [
                    'area' => 'frontend',
                    'theme' => 'magento_theme',
                    'namespace' => 'Magento',
                    'module' => 'Module',
                ],
            ],
            'no module' => [
                'frontend', 'magento_theme', 'en_US', null,
                [
                    'area' => 'frontend',
                    'theme' => 'magento_theme',
                    'locale' => 'en_US',
                ],
            ],
            'all params' => [
                'frontend', 'magento_theme', 'en_US', 'Magento_Module',
                [
                    'area' => 'frontend',
                    'theme' => 'magento_theme',
                    'locale' => 'en_US',
                    'namespace' => 'Magento',
                    'module' => 'Module',
                ],
            ],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File path '../file.ext' is forbidden for security reasons.
     */
    public function testResolveSecurityException()
    {
        $this->object->resolve('type', '../file.ext', '', null, '', '');
    }

    public function testResolveNoPatterns()
    {
        $this->cache->expects($this->once())
            ->method('getFromCache')
            ->with('type', 'file.ext', 'frontend', 'magento_theme', 'en_US', 'Magento_Module')
            ->will($this->returnValue(false));
        $this->rule->expects($this->once())
            ->method('getPatternDirs')
            ->will($this->returnValue([]));
        $this->cache->expects($this->once())
            ->method('saveToCache')
            ->with('', 'type', 'file.ext', 'frontend', 'magento_theme', 'en_US', 'Magento_Module');
        $this->assertFalse(
            $this->object->resolve(
                'type', 'file.ext', 'frontend', $this->getMockForTheme('magento_theme'), 'en_US', 'Magento_Module'
            )
        );
    }

    public function testResolveNonexistentFile()
    {
        $this->cache->expects($this->once())
            ->method('getFromCache')
            ->with('type', 'file.ext', 'frontend', 'magento_theme', 'en_US', 'Magento_Module')
            ->will($this->returnValue(false));
        $this->rule->expects($this->once())
            ->method('getPatternDirs')
            ->will($this->returnValue(['some/dir']));
        $this->directory->expects($this->once())
            ->method('isExist')
            ->will($this->returnValue(false));
        $this->cache->expects($this->once())
            ->method('saveToCache')
            ->with('', 'type', 'file.ext', 'frontend', 'magento_theme', 'en_US', 'Magento_Module');
        $this->assertFalse(
            $this->object->resolve(
                'type', 'file.ext', 'frontend', $this->getMockForTheme('magento_theme'), 'en_US', 'Magento_Module'
            )
        );
    }

    public function testResolveFromCache()
    {
        $expectedPath = '/some/dir/file.ext';

        $this->cache->expects($this->once())
            ->method('getFromCache')
            ->with('type', 'file.ext', 'frontend', 'magento_theme', 'en_US', 'Magento_Module')
            ->will($this->returnValue($expectedPath));
        $this->directory->expects($this->once())
            ->method('getAbsolutePath')
            ->with($expectedPath)
            ->will($this->returnValue($expectedPath));
        $this->rule->expects($this->never())
            ->method('getPatternDirs');
        $this->cache->expects($this->never())
            ->method('saveToCache');
        $actualPath = $this->object->resolve(
            'type', 'file.ext', 'frontend', $this->getMockForTheme('magento_theme'), 'en_US', 'Magento_Module'
        );
        $this->assertSame($expectedPath, $actualPath);
    }

    /**
     * @param string $themePath
     * @return \Magento\Framework\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockForTheme($themePath)
    {
        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($themePath));
        return $theme;
    }
}
