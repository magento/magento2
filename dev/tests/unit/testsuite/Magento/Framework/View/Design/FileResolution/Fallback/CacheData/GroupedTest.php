<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback\CacheData;

class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $theme;

    /**
     * @var Grouped
     */
    private $object;

    protected function setUp()
    {
        $this->cache = $this->getMock(
            '\Magento\Framework\View\Design\FileResolution\Fallback\Cache', [], [], '', false
        );

        $this->theme = $this->getMockForAbstractClass('\Magento\Framework\View\Design\ThemeInterface');

        $this->object = new \Magento\Framework\View\Design\FileResolution\Fallback\CacheData\Grouped($this->cache);
    }

    /**
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @param array $files
     *
     * @dataProvider getFromCacheDataProvider
     */
    public function testGetFromCache($area, $themePath, $locale, $module, array $files)
    {
        if (isset($params['theme'])) {
            $this->theme->expects($this->any())
                ->method('getThemePath')
                ->will($this->returnValue($params['theme']));
            $params['theme'] = $this->theme;
        } else {
            $this->theme->expects($this->never())
                ->method('getThemePath');
        }

        $cachedSections = [
            'type:file|area:frontend|theme:magento_theme|locale:en_US' => [
                'module:Magento_Module|file:file.ext' => 'one/file.ext',
                'module:Magento_Module|file:other_file.ext' => 'one/other_file.ext',
                'module:|file:file.ext' => 'two/file.ext',
                'module:|file:other_file.ext' => 'two/other_file.ext',
            ],
            'type:file|area:frontend|theme:magento_theme|locale:' => [
                'module:Magento_Module|file:file.ext' => 'three/file.ext',
                'module:Magento_Module|file:other_file.ext' => 'four/other_file.ext',
            ],
            'type:file|area:frontend|theme:|locale:en_US' => [
                'module:Magento_Module|file:file.ext' => 'five/file.ext',
                'module:Magento_Module|file:other_file.ext' => 'five/other_file.ext',
            ],
            'type:file|area:|theme:magento_theme|locale:en_US' => [
                'module:Magento_Module|file:file.ext' => 'seven/file.ext',
                'module:Magento_Module|file:other_file.ext' => 'other_file.ext',
            ],
        ];

        $this->cache->expects($this->once())
            ->method('load')
            ->will($this->returnCallback(function ($sectionId) use ($cachedSections) {
                if (!isset($cachedSections[$sectionId])) {
                    return false;
                }
                return json_encode($cachedSections[$sectionId]);
            }));

        foreach ($files as $requested => $expected) {
            $actual = $this->object->getFromCache('file', $requested, $area, $themePath, $locale, $module);
            $this->assertSame($expected, $actual);
        }
    }

    /**
     * @return array
     */
    public function getFromCacheDataProvider()
    {
        return [
            'all params' => [
                'frontend', 'magento_theme', 'en_US', 'Magento_Module',
                ['file.ext' => 'one/file.ext', 'other_file.ext' => 'one/other_file.ext'],
            ],
            'no area' => [
                null, 'magento_theme', 'en_US', 'Magento_Module',
                ['file.ext' => 'seven/file.ext', 'other_file.ext' => 'other_file.ext'],
            ],
            'no theme' => [
                'frontend', null, 'en_US', 'Magento_Module',
                ['file.ext' => 'five/file.ext', 'other_file.ext' => 'five/other_file.ext'],
            ],
            'no locale' => [
                'frontend', 'magento_theme', null, 'Magento_Module',
                ['file.ext' => 'three/file.ext', 'other_file.ext' => 'four/other_file.ext'],
            ],
            'no module' => [
                'frontend', 'magento_theme', 'en_US', null,
                ['file.ext' => 'two/file.ext', 'other_file.ext' => 'two/other_file.ext'],
            ],
        ];
    }

    /**
     * Verify that one and only one attempt to load cache is done even in case of cache absence
     */
    public function testGetFromCacheNothing()
    {
        $this->cache->expects($this->once())
            ->method('load');
        $this->assertFalse($this->object->getFromCache('type', 'file.ext',
            'frontend', 'magento_theme', 'en_US', 'Magento_Module'));
        $this->assertFalse($this->object->getFromCache('type', 'file.ext',
            'frontend', 'magento_theme', 'en_US', 'Magento_Module'));
    }

    /**
     * Ensure that cache is saved once and only once per section
     */
    public function testSaveToCache()
    {
        $this->cache->expects($this->exactly(2))
            ->method('save')
            ->will($this->returnValueMap([
                [
                    json_encode([
                        'module:Magento_Module|file:file.ext' => 'some/file.ext',
                        'module:Magento_Module|file:other_file.ext' => 'some/other_file.ext',
                    ]),
                    'type:file|area:frontend|theme:|locale:en_US',
                    true,
                ],
                [
                    json_encode(['module:Magento_Module|file:file.ext' => 'some/other/file.ext']),
                    'type:view|area:backend|theme:|locale:en_US',
                    true,
                ],
            ]));

        $this->object->saveToCache('some/file.ext', 'file', 'file.ext',
            'frontend', 'magento_theme', 'en_US', 'Magento_Module');
        $this->object->saveToCache('some/other_file.ext', 'file', 'other_file.ext',
            'frontend', 'magento_theme', 'en_US', 'Magento_Module');
        $this->object->saveToCache('some/other/file.ext', 'view', 'file.ext',
            'backend', 'magento_theme', 'en_US', 'Magento_Module');

        $this->object = null;
    }

    /**
     * Verify that no attempt to save cache is done, when nothing is updated
     */
    public function testSaveToCacheNothing()
    {
        $this->cache->expects($this->never())
            ->method('save');
        $this->object = null;
    }

    /**
     * Ensure that same data is not saved again
     */
    public function testSaveToCacheNotDirty()
    {
        $this->cache->expects($this->never())
            ->method('save');
        $this->cache->expects($this->once())
            ->method('load')
            ->with('type:file|area:frontend|theme:magento_theme|locale:en_US')
            ->will($this->returnValue(json_encode(['module:Magento_Module|file:file.ext' => 'some/file.ext'])));

        $this->object->saveToCache('some/file.ext', 'file', 'file.ext',
            'frontend', 'magento_theme', 'en_US', 'Magento_Module');

        $this->object = null;
    }
}
